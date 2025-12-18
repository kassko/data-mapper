<?php

declare(strict_types=1);

namespace Kassko\DataMapper\LazyLoader;

use Kassko\DataMapper\Attribute\DataSource;
use Kassko\DataMapper\Expression\ExpressionParser;
use Kassko\DataMapper\Expression\SourceFunctionProvider;
use Kassko\DataMapper\Metadata\AttributeReader;
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
    private ?ContainerInterface $container;

    public function __construct(?ContainerInterface $container = null)
    {
        $this->loadedProperties = new WeakMap();
        $this->sourceFunctionProviders = new WeakMap();
        $this->attributeReader = new AttributeReader();
        $this->container = $container;
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
        
        // Handle supplySeveralFields
        if ($dataSource->supplySeveralFields) {
            // Load all properties that reference this DataSource
            $this->loadPropertiesForDataSource($object, $dataSource);
        } else {
            // Old behavior: find properties with same signature
            $signature = $this->createSignature($dataSource, $object);
            $propertiesToHydrate = $this->findPropertiesWithSameSignature($object, $signature);
            
            // Load data from DataSource
            $data = $this->loadDataFromSource($dataSource, $object);
            
            // Hydrate all properties with the same signature
            foreach ($propertiesToHydrate as $propName) {
                $this->hydrateProperty($object, $propName, $data);
                $this->loadedProperties[$object][$propName] = true;
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
     * Load all properties that reference a DataSource with supplySeveralFields
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
            $fieldAttr = $this->attributeReader->readField($property);
            $fieldName = $fieldAttr !== null && $fieldAttr->name !== null ? $fieldAttr->name : $propName;
            
            // Hydrate if the field exists in the data
            if (array_key_exists($fieldName, $data)) {
                $property->setValue($object, $data[$fieldName]);
                $this->loadedProperties[$object][$propName] = true;
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
        $allProperties = $this->attributeReader->getPropertiesWithDataSource($object);
        
        foreach ($allProperties as $propName => $dataSource) {
            $propSignature = $this->createSignature($dataSource, $object);
            if ($propSignature === $signature) {
                $properties[] = $propName;
            }
        }
        
        return $properties;
    }

    /**
     * Load data from the DataSource
     *
     * @param DataSource $dataSource
     * @param object $object
     * @return array
     */
    private function loadDataFromSource(DataSource $dataSource, object $object): array
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
        
        $result = call_user_func_array(
            [$dataSourceInstance, $dataSource->method],
            $resolvedArgs
        );
        
        return is_array($result) ? $result : [];
    }

    /**
     * Resolve a DataSource class (either instantiate or get from container)
     *
     * @param string $class
     * @return object
     */
    private function resolveDataSource(string $class): object
    {
        // Check if it's a service identifier (prefixed with @)
        if (str_starts_with($class, '@')) {
            if ($this->container === null) {
                throw new \RuntimeException(
                    "Cannot resolve service identifier '{$class}' without a container"
                );
            }
            
            $serviceId = substr($class, 1);
            return $this->container->get($serviceId);
        }
        
        // Validate class exists and is instantiable before direct instantiation
        if (!class_exists($class)) {
            throw new \RuntimeException(
                "DataSource class '{$class}' does not exist"
            );
        }
        
        $reflectionClass = new ReflectionClass($class);
        if (!$reflectionClass->isInstantiable()) {
            throw new \RuntimeException(
                "DataSource class '{$class}' is not instantiable"
            );
        }
        
        return new $class();
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
        $expressionParser = new ExpressionParser($sourceFunctionProvider);
        
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
            throw new \RuntimeException(
                sprintf('DataSource with id "%s" not found in DataSourcesStore', $sourceId)
            );
        }
        
        $dataSource = $dataSourceMap[$sourceId];
        
        // Load the data from the source
        $result = $this->loadDataFromSource($dataSource, $object);
        
        // If supplySeveralFields, also hydrate all related properties
        if ($dataSource->supplySeveralFields && is_array($result)) {
            $this->loadPropertiesForDataSource($object, $dataSource);
        }
        
        return $result;
    }

    /**
     * Hydrate a property with data
     *
     * @param object $object
     * @param string $propertyName
     * @param array $data
     */
    private function hydrateProperty(object $object, string $propertyName, array $data): void
    {
        $reflectionClass = new ReflectionClass($object);
        
        if (!$reflectionClass->hasProperty($propertyName)) {
            return;
        }
        
        $property = $reflectionClass->getProperty($propertyName);
        
        // Get the field name mapping
        $fieldAttr = $this->attributeReader->readField($property);
        $fieldName = $fieldAttr !== null && $fieldAttr->name !== null ? $fieldAttr->name : $propertyName;
        
        if (!array_key_exists($fieldName, $data)) {
            return;
        }
        
        $property->setValue($object, $data[$fieldName]);
    }
}
