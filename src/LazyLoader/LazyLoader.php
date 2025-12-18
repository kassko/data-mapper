<?php

declare(strict_types=1);

namespace Kassko\DataMapper\LazyLoader;

use Kassko\DataMapper\Attribute\DataSource;
use Kassko\DataMapper\Metadata\AttributeReader;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionProperty;
use WeakMap;

class LazyLoader implements LazyLoaderInterface
{
    /** @var WeakMap<object, array<string, bool>> */
    private WeakMap $loadedProperties;
    
    private AttributeReader $attributeReader;
    private ?ContainerInterface $container;

    public function __construct(?ContainerInterface $container = null)
    {
        $this->loadedProperties = new WeakMap();
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
        
        // Check if property is already loaded
        if (isset($this->loadedProperties[$object][$propertyName])) {
            return;
        }
        
        // Get the property's DataSource attribute
        $reflectionClass = new ReflectionClass($object);
        
        if (!$reflectionClass->hasProperty($propertyName)) {
            return;
        }
        
        $property = $reflectionClass->getProperty($propertyName);
        $dataSource = $this->attributeReader->readDataSource($property);
        
        if ($dataSource === null) {
            return;
        }
        
        // Find all properties that share the same DataSource signature
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
        
        // Direct instantiation
        return new $class();
    }

    /**
     * Resolve arguments, replacing property references with actual values
     *
     * @param array $args
     * @param object $object
     * @return array
     */
    private function resolveArgs(array $args, object $object): array
    {
        $resolved = [];
        $reflectionClass = new ReflectionClass($object);
        
        foreach ($args as $arg) {
            if (is_string($arg) && str_starts_with($arg, '#')) {
                // Property reference - extract the value
                $propertyName = substr($arg, 1);
                
                if ($reflectionClass->hasProperty($propertyName)) {
                    $property = $reflectionClass->getProperty($propertyName);
                    $property->setAccessible(true);
                    $resolved[] = $property->getValue($object);
                } else {
                    $resolved[] = null;
                }
            } else {
                // Plain value
                $resolved[] = $arg;
            }
        }
        
        return $resolved;
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
        if (!isset($data[$propertyName])) {
            return;
        }
        
        $reflectionClass = new ReflectionClass($object);
        
        if (!$reflectionClass->hasProperty($propertyName)) {
            return;
        }
        
        $property = $reflectionClass->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($object, $data[$propertyName]);
    }
}
