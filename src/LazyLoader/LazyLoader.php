<?php

declare(strict_types=1);

namespace Kassko\DataMapper\LazyLoader;

use Kassko\DataMapper\Attribute\DataSource;
use Kassko\DataMapper\Attribute\Property;
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
        $dataSource = $this->resolveDataSourceForProperty($property, $object);
        
        if ($dataSource === null) {
            return;
        }
        
        // Handle supplySeveralProperties
        if ($dataSource->supplySeveralProperties) {
            // Load all properties that reference this DataSource
            $this->loadPropertiesForDataSource($object, $dataSource);
        } else {
            // Old behavior: find properties with same signature
            $signature = $this->createSignature($dataSource, $object);
            $propertiesToHydrate = $this->findPropertiesWithSameSignature($object, $signature);
            
            // Load data from DataSource
            $result = $this->executeDataSource($dataSource, $object);
            
            // If result is an array, use array-based hydration
            // Otherwise, directly set the value to all matching properties
            if (is_array($result)) {
                foreach ($propertiesToHydrate as $propName) {
                    $this->hydrateProperty($object, $propName, $result);
                    $this->loadedProperties[$object][$propName] = true;
                }
            } else {
                foreach ($propertiesToHydrate as $propName) {
                    $this->hydratePropertyWithValue($object, $propName, $result);
                    $this->loadedProperties[$object][$propName] = true;
                }
            }
        }
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
     * Load all properties that reference a DataSource with supplySeveralProps
     *
     * @param object $object
     * @param DataSource $dataSource
     */
    private function loadPropertiesForDataSource(object $object, DataSource $dataSource): void
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
            
            // Get the field name mapping
            $fieldName = $this->getPropertyNameMapping($property);
            
            // Hydrate if the field exists in the data
            if (array_key_exists($fieldName, $data)) {
                $value = $data[$fieldName];
                
                // Apply recursive hydration if needed
                $value = $this->applyRecursiveHydration($property, $value, 0);
                
                $property->setValue($object, $value);
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
     * Load data from the DataSource (expects array result for supplySeveralProps)
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
        
        // Get the field name mapping
        $fieldName = $this->getPropertyNameMapping($property);
        
        if (!array_key_exists($fieldName, $data)) {
            return;
        }
        
        $value = $data[$fieldName];
        
        // Check if we should perform recursive hydration
        $value = $this->applyRecursiveHydration($property, $value, $currentDepth);
        
        $property->setValue($object, $value);
        
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
        
        $property->setValue($object, $value);
        
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
        $propertyAttr = $this->attributeReader->readProperty($property);
        if ($propertyAttr === null || $propertyAttr->class === null) {
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
        
        // Get the actual class to instantiate
        $className = $propertyAttr->class;
        
        // Instantiate the nested object
        $nestedObject = new $className();
        
        // Get the actual class (in case of inheritance)
        $actualClass = get_class($nestedObject);
        
        // Hydrate the nested object recursively
        $this->hydrateObject($nestedObject, $value, $propertyAttr, $currentDepth + 1);
        
        return $nestedObject;
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
            
            $property->setValue($object, $value);
            
            // Handle Context attribute
            $this->handleContextAttribute($property, $value);
        }
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
}
