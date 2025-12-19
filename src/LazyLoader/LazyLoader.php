<?php

declare(strict_types=1);

namespace Kassko\DataMapper\LazyLoader;

use Kassko\DataMapper\Attribute\DataSource;
use Kassko\DataMapper\Attribute\Property;
use Kassko\DataMapper\Attribute\PropertyCandidates;
use Kassko\DataMapper\Attribute\Loading;
use Kassko\DataMapper\Expression\ExpressionParser;
use Kassko\DataMapper\Expression\SourceFunctionProvider;
use Kassko\DataMapper\Metadata\AttributeReader;
use Kassko\DataMapper\Registry\ContextRegistry;
use Kassko\DataMapper\ServiceResolver;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionProperty;
use WeakMap;

class LazyLoader implements LazyLoaderInterface
{
    /** @var WeakMap<object, array<string, bool>> */
    private WeakMap $loadedProperties;
    
    /** @var WeakMap<object, SourceFunctionProvider> */
    private WeakMap $sourceFunctionProviders;
    
    private AttributeReader $attributeReader;
    private ServiceResolver $serviceResolver;

    /**
     * @param ServiceResolver|ContainerInterface|null $serviceResolverOrContainer
     */
    public function __construct(ServiceResolver|ContainerInterface|null $serviceResolverOrContainer = null)
    {
        $this->loadedProperties = new WeakMap();
        $this->sourceFunctionProviders = new WeakMap();
        $this->attributeReader = new AttributeReader();
        
        // Support backward compatibility: allow ContainerInterface or null
        if ($serviceResolverOrContainer instanceof ServiceResolver) {
            $this->serviceResolver = $serviceResolverOrContainer;
        } elseif ($serviceResolverOrContainer instanceof ContainerInterface || $serviceResolverOrContainer === null) {
            // Backward compatibility: create a ServiceResolver with just the container
            $this->serviceResolver = new ServiceResolver($serviceResolverOrContainer, []);
        } else {
            throw new \InvalidArgumentException(
                'Argument must be ServiceResolver, ContainerInterface, or null'
            );
        }
    }

    public function getServiceResolver(): ServiceResolver
    {
        return $this->serviceResolver;
    }

    /**
     * Load all eager properties on the given object
     *
     * @param object $object The object to load eager properties for
     */
    public function loadEagerProperties(object $object): void
    {
        $reflectionClass = new ReflectionClass($object);
        
        // Get all properties including from parent classes
        $allProperties = $this->attributeReader->getAllProperties($reflectionClass);
        
        foreach ($allProperties as $property) {
            $loading = $this->attributeReader->readLoading($property);
            
            // Only load if marked as eager
            if ($loading !== null && $loading->type === Loading::TYPE_EAGER) {
                $this->loadProperty($object, $property->getName());
            }
        }
    }

    /**
     * Load a property on the given object
     *
     * @param object $object The object containing the property
     * @param string $propertyName The name of the property to load
     */
    public function loadProperty(object $object, string $propertyName): void
    {
        // Initialize loaded properties registry for this object if needed
        if (!isset($this->loadedProperties[$object])) {
            $this->loadedProperties[$object] = [];
        }
        
        // Initialize source function provider for this object if needed
        if (!isset($this->sourceFunctionProviders[$object])) {
            $this->sourceFunctionProviders[$object] = new SourceFunctionProvider(
                fn(string $sourceId) => $this->executeDataSourceById($object, $sourceId)
            );
        }
        
        // Check if property is already loaded
        if (isset($this->loadedProperties[$object][$propertyName])) {
            return;
        }
        
        // Get the property's DataSource
        $reflectionClass = new ReflectionClass($object);
        
        if (!$reflectionClass->hasProperty($propertyName)) {
            return;
        }
        
        $property = $reflectionClass->getProperty($propertyName);
        
        // Check for DataSourceRef with chain or providers
        $dataSourceRef = $this->attributeReader->readDataSourceRef($property);
        if ($dataSourceRef !== null && $dataSourceRef->chain !== null) {
            // Handle chain fallback
            $this->loadPropertyWithChain($object, $propertyName, $property, $dataSourceRef);
            return;
        } elseif ($dataSourceRef !== null && $dataSourceRef->providers !== null) {
            // Handle aggregation
            $this->loadPropertyWithProviders($object, $propertyName, $property, $dataSourceRef);
            return;
        }
        
        $dataSource = $this->resolveDataSourceForProperty($property, $object);
        
        if ($dataSource === null) {
            return;
        }
        
        // Handle supplySeveralProperties
        if ($dataSource->supplySeveralProperties) {
            // Load all properties that reference this DataSource
            $this->loadPropertiesForDataSource($object, $dataSource, $propertyName);
        } else {
            // Old behavior: find properties with same signature
            $signature = $this->createSignature($dataSource, $object);
            $propertiesToHydrate = $this->findPropertiesWithSameSignature($object, $signature);
            
            // Load data from DataSource
            $result = $this->executeDataSource($dataSource, $object);
            
            // If result is an associative array (not a list), use array-based hydration for multiple properties
            // If result is a list or non-array, treat it as a direct value for the property
            if (is_array($result) && !$this->isListArray($result) && count($result) > 0) {
                // Associative array - hydrate multiple properties from it
                foreach ($propertiesToHydrate as $propName) {
                    $this->hydrateProperty($object, $propName, $result);
                    $this->loadedProperties[$object][$propName] = true;
                }
            } else {
                // Direct value (could be a list, scalar, object, etc.) - set it directly to the property
                foreach ($propertiesToHydrate as $propName) {
                    $this->hydratePropertyWithValue($object, $propName, $result);
                    $this->loadedProperties[$object][$propName] = true;
                }
            }
        }
    }

    /**
     * Load a property with chain fallback mechanism
     *
     * @param object $object
     * @param string $propertyName
     * @param ReflectionProperty $property
     * @param \Kassko\DataMapper\Attribute\DataSourceRef $dataSourceRef
     */
    private function loadPropertyWithChain(object $object, string $propertyName, ReflectionProperty $property, $dataSourceRef): void
    {
        $dataSourceMap = $this->attributeReader->getDataSourceMap($object);
        $exceptionClass = $dataSourceRef->exception;
        
        foreach ($dataSourceRef->chain as $sourceId) {
            if (!isset($dataSourceMap[$sourceId])) {
                continue;
            }
            
            $dataSource = $dataSourceMap[$sourceId];
            
            try {
                $result = $this->executeDataSource($dataSource, $object);
                
                // Success! Hydrate the property
                if (is_array($result) && !$this->isListArray($result) && count($result) > 0) {
                    $this->hydrateProperty($object, $propertyName, $result);
                } else {
                    $this->hydratePropertyWithValue($object, $propertyName, $result);
                }
                
                $this->loadedProperties[$object][$propertyName] = true;
                return; // Successfully loaded, exit chain
            } catch (\Throwable $e) {
                // Check if this is the expected exception type
                if ($exceptionClass !== null && is_a($e, $exceptionClass)) {
                    // Continue to next source in chain
                    continue;
                }
                // If it's a different exception, rethrow it
                throw $e;
            }
        }
        
        // If we get here, all sources in the chain failed
        throw new \Kassko\DataMapper\Exception\NoValidDataSourceException(
            sprintf('No valid DataSource found in chain for property %s', $propertyName)
        );
    }

    /**
     * Load a property with aggregation from multiple providers
     *
     * @param object $object
     * @param string $propertyName
     * @param ReflectionProperty $property
     * @param \Kassko\DataMapper\Attribute\DataSourceRef $dataSourceRef
     */
    private function loadPropertyWithProviders(object $object, string $propertyName, ReflectionProperty $property, $dataSourceRef): void
    {
        $dataSourceMap = $this->attributeReader->getDataSourceMap($object);
        $aggregatedResult = [];
        
        foreach ($dataSourceRef->providers as $sourceId) {
            if (!isset($dataSourceMap[$sourceId])) {
                continue;
            }
            
            $dataSource = $dataSourceMap[$sourceId];
            $result = $this->executeDataSource($dataSource, $object);
            
            // Only aggregate arrays
            if (is_array($result)) {
                $aggregatedResult = array_replace_recursive($aggregatedResult, $result);
            }
        }
        
        // Hydrate the property with aggregated result
        if (!empty($aggregatedResult)) {
            if (!$this->isListArray($aggregatedResult)) {
                $this->hydrateProperty($object, $propertyName, $aggregatedResult);
            } else {
                $this->hydratePropertyWithValue($object, $propertyName, $aggregatedResult);
            }
        }
        
        $this->loadedProperties[$object][$propertyName] = true;
    }

    /**
     * Resolve the DataSource for a property (either from attribute or from store via ref)
     *
     * @param ReflectionProperty $property
     * @param object $object
     * @return DataSource|null
     */
    private function resolveDataSourceForProperty(ReflectionProperty $property, object $object): ?DataSource
    {
        // First check for direct DataSource attribute
        $dataSource = $this->attributeReader->readDataSource($property);
        if ($dataSource !== null) {
            return $dataSource;
        }
        
        // Check for DataSourceRef attribute
        $dataSourceRef = $this->attributeReader->readDataSourceRef($property);
        if ($dataSourceRef !== null) {
            $dataSourceMap = $this->attributeReader->getDataSourceMap($object);
            return $dataSourceMap[$dataSourceRef->id] ?? null;
        }
        
        return null;
    }

    /**
     * Load all properties that reference a DataSource with supplySeveralProperties
     *
     * @param object $object
     * @param DataSource $dataSource
     * @param string $triggeringPropertyName The property that triggered this load
     */
    private function loadPropertiesForDataSource(object $object, DataSource $dataSource, string $triggeringPropertyName = ''): void
    {
        // Load data from DataSource once
        $data = $this->loadDataFromSource($dataSource, $object);
        
        if (!is_array($data)) {
            return;
        }
        
        // Find all properties that reference this DataSource
        $reflectionClass = new ReflectionClass($object);
        
        foreach ($reflectionClass->getProperties() as $property) {
            $propDataSource = $this->resolveDataSourceForProperty($property, $object);
            
            // Check if this property uses the same DataSource (by id)
            if ($propDataSource === null || $propDataSource->id === null || $propDataSource->id !== $dataSource->id) {
                continue;
            }
            
            // Check if already loaded
            $propName = $property->getName();
            if (isset($this->loadedProperties[$object][$propName])) {
                continue;
            }
            
            // Check loading scope
            if (!$this->shouldHydratePropertyInScope($dataSource, $propName, $triggeringPropertyName)) {
                continue;
            }
            
            // Check if property has instance mapping
            $propertyAttr = $this->attributeReader->readProperty($property);
            if ($propertyAttr !== null && $propertyAttr->mapping !== null) {
                // Extract data using mapping from flat parent data
                $mappedData = $this->applyInstanceMapping($data, $propertyAttr);
                
                // Only proceed if we have mapped data
                if (!empty($mappedData)) {
                    $value = $this->applyRecursiveHydration($property, $mappedData, 0);
                    $this->setPropertyValue($object, $property, $value);
                    $this->loadedProperties[$object][$propName] = true;
                    $this->handleContextAttribute($property, $value);
                }
                continue;
            }
            
            // Get the field name mapping
            $fieldName = $this->getPropertyNameMapping($property);
            
            // Hydrate if the field exists in the data
            if (array_key_exists($fieldName, $data)) {
                $value = $data[$fieldName];
                
                // Apply recursive hydration if needed
                $value = $this->applyRecursiveHydration($property, $value, 0);
                
                // Set property value using setter resolution
                $this->setPropertyValue($object, $property, $value);
                $this->loadedProperties[$object][$propName] = true;
                
                // Handle Context attribute
                $this->handleContextAttribute($property, $value);
            }
        }
    }

    /**
     * Create a unique signature for a DataSource configuration
     *
     * @param DataSource $dataSource
     * @param object $object
     * @return string
     */
    private function createSignature(DataSource $dataSource, object $object): string
    {
        $resolvedArgs = $this->resolveArgs($dataSource->args, $object);
        return md5(serialize([
            'class' => $dataSource->class,
            'method' => $dataSource->method,
            'args' => $resolvedArgs
        ]));
    }

    /**
     * Find all properties that share the same DataSource signature
     *
     * @param object $object
     * @param string $signature
     * @return array<string>
     */
    private function findPropertiesWithSameSignature(object $object, string $signature): array
    {
        $properties = [];
        $reflectionClass = new ReflectionClass($object);
        
        // Check all properties for matching DataSource signature
        foreach ($reflectionClass->getProperties() as $property) {
            $dataSource = $this->resolveDataSourceForProperty($property, $object);
            
            if ($dataSource === null) {
                continue;
            }
            
            $propSignature = $this->createSignature($dataSource, $object);
            if ($propSignature === $signature) {
                $properties[] = $property->getName();
            }
        }
        
        return $properties;
    }

    /**
     * Load data from the DataSource (expects array result for supplySeveralProperties)
     *
     * @param DataSource $dataSource
     * @param object $object
     * @return array
     */
    private function loadDataFromSource(DataSource $dataSource, object $object): array
    {
        $result = $this->executeDataSource($dataSource, $object);
        return is_array($result) ? $result : [];
    }

    /**
     * Execute a DataSource and return the result
     *
     * @param DataSource $dataSource
     * @param object $object
     * @return mixed
     */
    private function executeDataSource(DataSource $dataSource, object $object)
    {
        $dataSourceInstance = $this->resolveDataSource($dataSource->class);
        $resolvedArgs = $this->resolveArgs($dataSource->args, $object);
        
        // Validate method exists before calling
        if (!method_exists($dataSourceInstance, $dataSource->method)) {
            throw new \RuntimeException(
                sprintf(
                    'Method %s does not exist on DataSource class %s',
                    $dataSource->method,
                    get_class($dataSourceInstance)
                )
            );
        }
        
        return call_user_func_array(
            [$dataSourceInstance, $dataSource->method],
            $resolvedArgs
        );
    }

    /**
     * Resolve a DataSource class (either instantiate or get from container)
     *
     * @param string $class
     * @return object
     */
    private function resolveDataSource(string $class): object
    {
        return $this->serviceResolver->resolve($class);
    }

    /**
     * Resolve arguments, replacing property references and expressions with actual values
     *
     * @param array $args
     * @param object $object
     * @return array
     */
    private function resolveArgs(array $args, object $object): array
    {
        $sourceFunctionProvider = $this->sourceFunctionProviders[$object];
        $expressionParser = new ExpressionParser($sourceFunctionProvider, $this->serviceResolver);
        
        return $expressionParser->resolveArgs($args, $object, function(string $propertyName) use ($object) {
            $this->loadProperty($object, $propertyName);
        });
    }

    /**
     * Execute a DataSource by its id from the store
     *
     * @param object $object
     * @param string $sourceId
     * @return mixed
     */
    private function executeDataSourceById(object $object, string $sourceId)
    {
        $dataSourceMap = $this->attributeReader->getDataSourceMap($object);
        
        if (!isset($dataSourceMap[$sourceId])) {
            // Sanitize sourceId for error message to prevent log injection
            $sanitizedId = preg_replace('/[^a-zA-Z0-9_-]/', '', $sourceId);
            throw new \RuntimeException(
                sprintf('DataSource with id "%s" not found in DataSourcesStore', $sanitizedId)
            );
        }
        
        $dataSource = $dataSourceMap[$sourceId];
        
        // Execute the data source
        $result = $this->executeDataSource($dataSource, $object);
        
        // If supplySeveralProperties, also hydrate all related properties
        if ($dataSource->supplySeveralProperties && is_array($result)) {
            $this->loadPropertiesForDataSource($object, $dataSource);
        }
        
        return $result;
    }

    /**
     * Hydrate a property with data from an array
     *
     * @param object $object
     * @param string $propertyName
     * @param array $data
     * @param int $currentDepth Current recursion depth
     */
    private function hydrateProperty(object $object, string $propertyName, array $data, int $currentDepth = 0): void
    {
        $reflectionClass = new ReflectionClass($object);
        
        if (!$reflectionClass->hasProperty($propertyName)) {
            return;
        }
        
        $property = $reflectionClass->getProperty($propertyName);
        
        // Check if property has instance mapping
        $propertyAttr = $this->attributeReader->readProperty($property);
        if ($propertyAttr !== null && $propertyAttr->mapping !== null) {
            // Extract data using mapping from flat parent data
            $mappedData = $this->applyInstanceMapping($data, $propertyAttr);
            
            // Only proceed if we have mapped data
            if (!empty($mappedData)) {
                $value = $this->applyRecursiveHydration($property, $mappedData, $currentDepth);
                $this->setPropertyValue($object, $property, $value);
                $this->handleContextAttribute($property, $value);
            }
            return;
        }
        
        // Get the field name mapping
        $fieldName = $this->getPropertyNameMapping($property);
        
        if (!array_key_exists($fieldName, $data)) {
            return;
        }
        
        $value = $data[$fieldName];
        
        // Check if we should perform recursive hydration
        $value = $this->applyRecursiveHydration($property, $value, $currentDepth);
        
        // Set property value using setter resolution
        $this->setPropertyValue($object, $property, $value);
        
        // Handle Context attribute
        $this->handleContextAttribute($property, $value);
    }

    /**
     * Hydrate a property with a single value
     *
     * @param object $object
     * @param string $propertyName
     * @param mixed $value
     * @param int $currentDepth Current recursion depth
     */
    private function hydratePropertyWithValue(object $object, string $propertyName, $value, int $currentDepth = 0): void
    {
        $reflectionClass = new ReflectionClass($object);
        
        if (!$reflectionClass->hasProperty($propertyName)) {
            return;
        }
        
        $property = $reflectionClass->getProperty($propertyName);
        
        // Check if we should perform recursive hydration
        $value = $this->applyRecursiveHydration($property, $value, $currentDepth);
        
        // Set property value using setter resolution
        $this->setPropertyValue($object, $property, $value);
        
        // Handle Context attribute
        $this->handleContextAttribute($property, $value);
    }

    /**
     * Get the field/property name mapping from attributes
     * Supports both Property (new) and Field (backward compatibility) attributes
     *
     * @param ReflectionProperty $property
     * @return string
     */
    private function getPropertyNameMapping(ReflectionProperty $property): string
    {
        // Try Property attribute first
        $propertyAttr = $this->attributeReader->readProperty($property);
        if ($propertyAttr !== null && $propertyAttr->name !== null) {
            return $propertyAttr->name;
        }
        
        // Fall back to Field attribute for backward compatibility
        $fieldAttr = $this->attributeReader->readField($property);
        if ($fieldAttr !== null && $fieldAttr->name !== null) {
            return $fieldAttr->name;
        }
        
        // Default to property name
        return $property->getName();
    }

    /**
     * Apply recursive hydration if needed
     *
     * @param ReflectionProperty $property
     * @param mixed $value
     * @param int $currentDepth
     * @return mixed
     */
    private function applyRecursiveHydration(ReflectionProperty $property, mixed $value, int $currentDepth): mixed
    {
        // Check for PropertyCandidates first
        $propertyCandidates = $this->attributeReader->readPropertyCandidates($property);
        $propertyAttr = null;
        
        // Note: Don't try to resolve PropertyCandidate here for the whole value
        // It will be resolved per-item if this is a list array
        
        // Fall back to direct Property attribute
        $propertyAttr = $this->attributeReader->readProperty($property);
        
        // If no direct Property attribute and we have PropertyCandidates, 
        // we'll handle it in the list processing below
        if ($propertyAttr === null && $propertyCandidates === null) {
            return $value;
        }
        
        // Check depth limit
        $loading = $this->attributeReader->readLoading($property);
        if ($loading !== null && $loading->depth !== null && $currentDepth >= $loading->depth) {
            return $value;
        }
        
        // If value is not an array, can't hydrate
        if (!is_array($value)) {
            return $value;
        }
        
        // Handle array of objects (collection)
        if ($this->isListArray($value)) {
            $result = [];
            foreach ($value as $itemData) {
                if (!is_array($itemData)) {
                    $result[] = $itemData;
                    continue;
                }
                
                // Resolve property config for each item if PropertyCandidates exists
                $itemPropertyAttr = $propertyAttr;
                if ($propertyCandidates !== null) {
                    $resolved = $this->resolvePropertyCandidate($propertyCandidates, $itemData);
                    if ($resolved !== null) {
                        $itemPropertyAttr = $resolved;
                    }
                }
                
                // If still no property config, skip this item
                if ($itemPropertyAttr === null || $itemPropertyAttr->class === null) {
                    $result[] = $itemData;
                    continue;
                }
                
                // Instantiate the nested object
                $nestedObject = new $itemPropertyAttr->class();
                
                // Execute after_create_object hooks
                $this->executeClassHooks($nestedObject, 'after_create_object', $itemData);
                
                // Hydrate the nested list item (using mapped data keys)
                $this->hydrateObject($nestedObject, $itemData, $itemPropertyAttr, $currentDepth + 1);
                
                $result[] = $nestedObject;
            }
            return $result;
        }
        
        // Single object (not a list)
        // If no propertyAttr and we have PropertyCandidates, try to resolve
        if ($propertyAttr === null && $propertyCandidates !== null) {
            $propertyAttr = $this->resolvePropertyCandidate($propertyCandidates, $value);
        }
        
        // If still no propertyAttr or no class, return as-is
        if ($propertyAttr === null || $propertyAttr->class === null) {
            return $value;
        }
        
        // Get the actual class to instantiate
        $className = $propertyAttr->class;
        
        // Instantiate the nested object
        $nestedObject = new $className();
        
        // Execute after_create_object hooks
        $this->executeClassHooks($nestedObject, 'after_create_object', $value);
        
        // Hydrate the single nested object (using mapped data keys)
        $this->hydrateObject($nestedObject, $value, $propertyAttr, $currentDepth + 1);
        
        return $nestedObject;
    }

    /**
     * Resolve property configuration from PropertyCandidates based on discriminator evaluation
     *
     * @param PropertyCandidates $propertyCandidates
     * @param array $rawDataItem
     * @return Property|null
     */
    private function resolvePropertyCandidate(PropertyCandidates $propertyCandidates, array $rawDataItem): ?Property
    {
        // Create a temporary expression parser for discriminator evaluation
        $sourceFunctionProvider = new SourceFunctionProvider(fn() => null);
        $expressionParser = new ExpressionParser($sourceFunctionProvider, $this->serviceResolver);
        $expressionParser->setRawData($rawDataItem);
        
        foreach ($propertyCandidates->candidates as $candidate) {
            // Evaluate the discriminator expression
            $discriminator = $candidate->discriminator;
            
            // Check if it's an expression
            if (preg_match('/^expr\((.+)\)$/s', $discriminator, $matches)) {
                $expression = $matches[1];
                
                // Parse and evaluate the expression
                // For rawDataItemExists and rawDataItem, we need to evaluate manually
                if (preg_match("/rawDataItemExists\('([^']+)'\)/", $expression, $keyMatches)) {
                    $key = $keyMatches[1];
                    $result = array_key_exists($key, $rawDataItem);
                } elseif (preg_match("/rawDataItem\('([^']+)'\)/", $expression, $keyMatches)) {
                    // Support rawDataItem() expressions
                    $key = $keyMatches[1];
                    $value = $rawDataItem[$key] ?? null;
                    // If followed by comparison, evaluate it
                    if (preg_match("/rawDataItem\('[^']+'\)\s*==\s*'([^']+)'/", $expression, $eqMatches)) {
                        $result = ($value == $eqMatches[1]);
                    } else {
                        $result = (bool)$value;
                    }
                } elseif (preg_match("/context\('([^']+)'\)/", $expression, $keyMatches)) {
                    $key = $keyMatches[1];
                    $contextValue = ContextRegistry::get($key);
                    // Evaluate the full expression - for now handle simple equality
                    if (preg_match("/context\('([^']+)'\)\s*==\s*'([^']+)'/", $expression, $eqMatches)) {
                        $result = ($contextValue == $eqMatches[2]);
                    } else {
                        $result = (bool)$contextValue;
                    }
                } else {
                    // For other expressions, attempt basic evaluation
                    // This is a fallback - complex expressions may need additional handling
                    $result = false;
                }
                
                if ($result === true) {
                    return $candidate->property;
                }
            }
        }
        
        return null;
    }

    /**
     * Hydrate an object with data
     *
     * @param object $object
     * @param array $data
     * @param Property|null $propertyAttr Property attribute for expand/noExpand control
     * @param int $currentDepth Current recursion depth
     */
    private function hydrateObject(object $object, array $data, ?Property $propertyAttr = null, int $currentDepth = 0): void
    {
        $reflectionClass = new ReflectionClass($object);
        
        // Get all properties including from parent classes
        $allProperties = $this->attributeReader->getAllProperties($reflectionClass);
        
        // Build list of properties to expand or skip
        $expandList = $propertyAttr !== null && $propertyAttr->expand !== null 
            ? array_map('trim', explode(',', $propertyAttr->expand)) 
            : null;
        $noExpandList = $propertyAttr !== null && $propertyAttr->noExpand !== null 
            ? array_map('trim', explode(',', $propertyAttr->noExpand)) 
            : null;
        
        foreach ($allProperties as $property) {
            $propName = $property->getName();
            
            // Check if we should hydrate this property based on expand/noExpand
            if ($expandList !== null && !in_array($propName, $expandList)) {
                continue;
            }
            if ($noExpandList !== null && in_array($propName, $noExpandList)) {
                continue;
            }
            
            // Check if property should be hydrated based on attributes
            if (!$this->attributeReader->shouldHydrateProperty($reflectionClass, $property)) {
                continue;
            }
            
            // Get the field name mapping
            $fieldName = $this->getPropertyNameMapping($property);
            
            if (!array_key_exists($fieldName, $data)) {
                continue;
            }
            
            $value = $data[$fieldName];
            
            // Apply recursive hydration if needed
            $value = $this->applyRecursiveHydration($property, $value, $currentDepth);
            
            // Set property value using setter resolution
            $this->setPropertyValue($object, $property, $value);
            
            // Handle Context attribute
            $this->handleContextAttribute($property, $value);
        }
    }

    /**
     * Set a property value using setter resolution logic
     *
     * @param object $object
     * @param ReflectionProperty $property
     * @param mixed $value
     */
    private function setPropertyValue(object $object, ReflectionProperty $property, mixed $value): void
    {
        $propertyName = $property->getName();
        
        // Execute before_set_property hooks
        $this->executePropertyHooks($object, $property, 'before_set_property', $value);
        
        // 1. Check for explicit Setter attribute
        $setter = $this->attributeReader->readSetter($property);
        if ($setter !== null && $setter->name !== null) {
            // Use explicit setter method
            if (method_exists($object, $setter->name)) {
                $object->{$setter->name}($value);
                // Execute after_set_property hooks
                $this->executePropertyHooks($object, $property, 'after_set_property', $value);
                return;
            }
        }
        
        // 2. If value is a non-associative array (list), look for adder method
        if (is_array($value) && $this->isListArray($value)) {
            $adderMethod = 'add' . ucfirst($propertyName) . 'Item';
            if (method_exists($object, $adderMethod)) {
                foreach ($value as $item) {
                    $object->$adderMethod($item);
                }
                // Execute after_set_property hooks
                $this->executePropertyHooks($object, $property, 'after_set_property', $value);
                return;
            }
        }
        
        // 3. Look for setter method
        $setterMethod = 'set' . ucfirst($propertyName);
        if (method_exists($object, $setterMethod)) {
            $object->$setterMethod($value);
            // Execute after_set_property hooks
            $this->executePropertyHooks($object, $property, 'after_set_property', $value);
            return;
        }
        
        // 4. Fall back to direct property assignment via reflection
        $property->setValue($object, $value);
        
        // Execute after_set_property hooks
        $this->executePropertyHooks($object, $property, 'after_set_property', $value);
    }

    /**
     * Check if an array is a list (non-associative array with sequential numeric keys)
     *
     * @param array $array
     * @return bool
     */
    private function isListArray(array $array): bool
    {
        return array_is_list($array);
    }

    /**
     * Handle Context attribute - set context values when property is loaded
     *
     * @param ReflectionProperty $property
     * @param mixed $value
     */
    private function handleContextAttribute(ReflectionProperty $property, mixed $value): void
    {
        $context = $this->attributeReader->readContext($property);
        
        if ($context === null) {
            return;
        }
        
        // Set all context values
        ContextRegistry::setMany($context->values);
    }

    /**
     * Execute hooks for a specific event on a class
     *
     * @param object $object
     * @param string $hookName
     * @param array $rawData Optional raw data for expression evaluation
     */
    private function executeClassHooks(object $object, string $hookName, array $rawData = []): void
    {
        $reflectionClass = new ReflectionClass($object);
        $hooks = $this->attributeReader->readClassHooks($reflectionClass);
        
        foreach ($hooks as $hook) {
            if ($hook->name === $hookName) {
                $this->executeHook($object, $hook, $rawData);
            }
        }
    }

    /**
     * Execute hooks for a specific event on a property
     *
     * @param object $object
     * @param ReflectionProperty $property
     * @param string $hookName
     * @param mixed $propertyValue Optional property value to pass
     */
    private function executePropertyHooks(object $object, ReflectionProperty $property, string $hookName, mixed $propertyValue = null): void
    {
        $hooks = $this->attributeReader->readPropertyHooks($property);
        
        foreach ($hooks as $hook) {
            if ($hook->name === $hookName) {
                // Add property value to raw data for expression evaluation
                $rawData = [$property->getName() => $propertyValue];
                $this->executeHook($object, $hook, $rawData, $property);
            }
        }
    }

    /**
     * Execute a single hook
     *
     * @param object $object
     * @param \Kassko\DataMapper\Attribute\Hook $hook
     * @param array $rawData Raw data for expression evaluation
     * @param ReflectionProperty|null $property Optional property for #property reference
     */
    private function executeHook(object $object, \Kassko\DataMapper\Attribute\Hook $hook, array $rawData = [], ?ReflectionProperty $property = null): void
    {
        // Determine which object/service to call the method on
        $target = $object;
        if ($hook->class !== null) {
            // External service/class
            $target = $this->serviceResolver->resolve($hook->class);
        }
        
        // Resolve hook arguments
        $sourceFunctionProvider = $this->sourceFunctionProviders[$object] ?? new SourceFunctionProvider(
            fn(string $sourceId) => $this->executeDataSourceById($object, $sourceId)
        );
        $expressionParser = new ExpressionParser($sourceFunctionProvider, $this->serviceResolver);
        $expressionParser->setRawData($rawData);
        $expressionParser->setCurrentObject($object);
        
        // Create a property loader callback
        $propertyLoader = function(string $propertyName) use ($object) {
            $this->loadProperty($object, $propertyName);
        };
        
        // Resolve arguments, but handle #property reference specially
        $resolvedArgs = [];
        foreach ($hook->args as $arg) {
            if ($property !== null && $arg === '#' . $property->getName()) {
                // Special case: reference to the property being set
                // Make property accessible before getting value
                $property->setAccessible(true);
                $resolvedArgs[] = $property->getValue($object);
            } else {
                // Use standard resolution via expression parser
                $resolved = is_string($arg) ? $this->resolveSingleArg($arg, $object, $propertyLoader, $expressionParser) : $arg;
                $resolvedArgs[] = $resolved;
            }
        }
        
        // Call the hook method
        if (method_exists($target, $hook->method)) {
            call_user_func_array([$target, $hook->method], $resolvedArgs);
        }
    }

    /**
     * Resolve a single argument using the expression parser
     *
     * @param string $arg
     * @param object $object
     * @param callable $propertyLoader
     * @param ExpressionParser $expressionParser
     * @return mixed
     */
    private function resolveSingleArg(string $arg, object $object, callable $propertyLoader, ExpressionParser $expressionParser)
    {
        // Handle ##this syntax (return current object)
        if ($arg === '##this') {
            return $object;
        }

        // Handle #property syntax
        if (str_starts_with($arg, '#')) {
            $propertyName = substr($arg, 1);
            $propertyLoader($propertyName);
            
            $reflectionClass = new ReflectionClass($object);
            if ($reflectionClass->hasProperty($propertyName)) {
                $property = $reflectionClass->getProperty($propertyName);
                return $property->getValue($object);
            }
            return null;
        }

        // Handle expr(...) syntax
        if (preg_match('/^expr\((.+)\)$/s', $arg, $matches)) {
            // Re-use the expression parser's existing resolveArgs method
            // which will properly handle the expr() wrapper
            return $expressionParser->resolveArgs([$arg], $object, $propertyLoader)[0];
        }

        // Plain value
        return $arg;
    }

    /**
     * Check if a property should be hydrated based on loading scope
     *
     * @param DataSource $dataSource
     * @param string $propertyName
     * @param string $triggeringPropertyName
     * @return bool
     */
    private function shouldHydratePropertyInScope(DataSource $dataSource, string $propertyName, string $triggeringPropertyName): bool
    {
        switch ($dataSource->loadingScope) {
            case DataSource::SCOPE_PROPERTY:
                // Only load the triggering property
                return $propertyName === $triggeringPropertyName;
                
            case DataSource::SCOPE_ONLY_KEYS:
                // Only load specified keys
                return in_array($propertyName, $dataSource->loadingScopeKeys, true);
                
            case DataSource::SCOPE_EXCEPT_KEYS:
                // Load all except specified keys
                return !in_array($propertyName, $dataSource->loadingScopeKeys, true);
                
            case DataSource::SCOPE_ALL:
            default:
                // Load all properties
                return true;
        }
    }

    /**
     * Apply instance-specific mapping to data
     *
     * @param array $data
     * @param Property|null $propertyAttr
     * @return array
     */
    private function applyInstanceMapping(array $data, ?Property $propertyAttr): array
    {
        // If no mapping specified, return data as-is
        if ($propertyAttr === null || $propertyAttr->mapping === null) {
            return $data;
        }
        
        // Transform keys according to mapping
        // mapping format: ['source_key' => 'target_key', ...]
        $mappedData = [];
        foreach ($propertyAttr->mapping as $sourceKey => $targetKey) {
            if (array_key_exists($sourceKey, $data)) {
                $mappedData[$targetKey] = $data[$sourceKey];
            }
        }
        
        return $mappedData;
    }
}
