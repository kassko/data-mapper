<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Metadata;

use Kassko\DataMapper\Attribute\DataSource;
use Kassko\DataMapper\Attribute\DataSourceRef;
use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\Attribute\Field;
use Kassko\DataMapper\Attribute\Property;
use Kassko\DataMapper\Attribute\Loading;
use Kassko\DataMapper\Attribute\SkipProperty;
use Kassko\DataMapper\Attribute\SkipAllProperties;
use Kassko\DataMapper\Attribute\KeepAllProperties;
use ReflectionClass;
use ReflectionProperty;

class AttributeReader
{
    /**
     * Read DataSource attribute from a property
     *
     * @param ReflectionProperty $property
     * @return DataSource|null
     */
    public function readDataSource(ReflectionProperty $property): ?DataSource
    {
        $attributes = $property->getAttributes(DataSource::class);
        
        if (empty($attributes)) {
            return null;
        }
        
        return $attributes[0]->newInstance();
    }

    /**
     * Read DataSourceRef attribute from a property
     *
     * @param ReflectionProperty $property
     * @return DataSourceRef|null
     */
    public function readDataSourceRef(ReflectionProperty $property): ?DataSourceRef
    {
        $attributes = $property->getAttributes(DataSourceRef::class);
        
        if (empty($attributes)) {
            return null;
        }
        
        return $attributes[0]->newInstance();
    }

    /**
     * Read Field attribute from a property (backward compatibility)
     *
     * @param ReflectionProperty $property
     * @return Field|null
     */
    public function readField(ReflectionProperty $property): ?Field
    {
        $attributes = $property->getAttributes(Field::class);
        
        if (empty($attributes)) {
            return null;
        }
        
        return $attributes[0]->newInstance();
    }

    /**
     * Read Property attribute from a property
     *
     * @param ReflectionProperty $property
     * @return Property|null
     */
    public function readProperty(ReflectionProperty $property): ?Property
    {
        $attributes = $property->getAttributes(Property::class);
        
        if (empty($attributes)) {
            return null;
        }
        
        return $attributes[0]->newInstance();
    }

    /**
     * Read Loading attribute from a property
     *
     * @param ReflectionProperty $property
     * @return Loading|null
     */
    public function readLoading(ReflectionProperty $property): ?Loading
    {
        $attributes = $property->getAttributes(Loading::class);
        
        if (empty($attributes)) {
            return null;
        }
        
        return $attributes[0]->newInstance();
    }

    /**
     * Read SkipProperty attribute from a property
     *
     * @param ReflectionProperty $property
     * @return SkipProperty|null
     */
    public function readSkipProperty(ReflectionProperty $property): ?SkipProperty
    {
        $attributes = $property->getAttributes(SkipProperty::class);
        
        if (empty($attributes)) {
            return null;
        }
        
        return $attributes[0]->newInstance();
    }

    /**
     * Check if class has SkipAllProperties attribute
     *
     * @param ReflectionClass $reflectionClass
     * @return bool
     */
    public function hasSkipAllProperties(ReflectionClass $reflectionClass): bool
    {
        return !empty($reflectionClass->getAttributes(SkipAllProperties::class));
    }

    /**
     * Check if class has KeepAllProperties attribute
     *
     * @param ReflectionClass $reflectionClass
     * @return bool
     */
    public function hasKeepAllProperties(ReflectionClass $reflectionClass): bool
    {
        return !empty($reflectionClass->getAttributes(KeepAllProperties::class));
    }

    /**
     * Read DataSourcesStore attribute from a class
     *
     * @param ReflectionClass $reflectionClass
     * @return DataSourcesStore|null
     */
    public function readDataSourcesStore(ReflectionClass $reflectionClass): ?DataSourcesStore
    {
        $attributes = $reflectionClass->getAttributes(DataSourcesStore::class);
        
        if (empty($attributes)) {
            return null;
        }
        
        return $attributes[0]->newInstance();
    }

    /**
     * Build a map of DataSource id => DataSource from DataSourcesStore or repeatable DataSource attributes
     *
     * @param object $object
     * @return array<string, DataSource>
     */
    public function getDataSourceMap(object $object): array
    {
        $reflectionClass = new ReflectionClass($object);
        $map = [];
        
        // First try DataSourcesStore (backward compatibility)
        $store = $this->readDataSourcesStore($reflectionClass);
        if ($store !== null) {
            foreach ($store->sources as $source) {
                if ($source->id !== null) {
                    $map[$source->id] = $source;
                }
            }
        }
        
        // Also check for repeatable DataSource attributes on the class
        $dataSourceAttrs = $reflectionClass->getAttributes(DataSource::class);
        foreach ($dataSourceAttrs as $attr) {
            $source = $attr->newInstance();
            if ($source->id !== null) {
                $map[$source->id] = $source;
            }
        }
        
        return $map;
    }

    /**
     * Get all properties with DataSource attributes from an object
     *
     * @param object $object
     * @return array<string, DataSource> Array of property name => DataSource
     */
    public function getPropertiesWithDataSource(object $object): array
    {
        $reflectionClass = new ReflectionClass($object);
        $properties = [];
        
        foreach ($reflectionClass->getProperties() as $property) {
            $dataSource = $this->readDataSource($property);
            if ($dataSource !== null) {
                $properties[$property->getName()] = $dataSource;
            }
        }
        
        return $properties;
    }

    /**
     * Get all properties from a class including parent classes
     *
     * @param ReflectionClass $reflectionClass
     * @return array<string, ReflectionProperty>
     */
    public function getAllProperties(ReflectionClass $reflectionClass): array
    {
        $properties = [];
        
        $class = $reflectionClass;
        while ($class !== false) {
            foreach ($class->getProperties() as $property) {
                // Don't override child properties with parent ones
                if (!isset($properties[$property->getName()])) {
                    $properties[$property->getName()] = $property;
                }
            }
            $class = $class->getParentClass();
        }
        
        return $properties;
    }

    /**
     * Get properties defined in traits
     *
     * @param ReflectionClass $reflectionClass
     * @return array<string, ReflectionProperty>
     */
    public function getTraitProperties(ReflectionClass $reflectionClass): array
    {
        $traitProperties = [];
        
        foreach ($reflectionClass->getTraits() as $trait) {
            foreach ($trait->getProperties() as $property) {
                $traitProperties[$property->getName()] = $property;
            }
            // Recursively get traits used by traits
            $traitProperties = array_merge($traitProperties, $this->getTraitProperties($trait));
        }
        
        return $traitProperties;
    }

    /**
     * Check if a property should be hydrated based on class and property attributes
     *
     * @param ReflectionClass $reflectionClass
     * @param ReflectionProperty $property
     * @return bool
     */
    public function shouldHydrateProperty(ReflectionClass $reflectionClass, ReflectionProperty $property): bool
    {
        $hasSkipProperty = $this->readSkipProperty($property) !== null;
        $hasProperty = $this->readProperty($property) !== null;
        $hasSkipAllProperties = $this->hasSkipAllProperties($reflectionClass);
        
        // If property has SkipProperty, never hydrate
        if ($hasSkipProperty) {
            return false;
        }
        
        // If class has SkipAllProperties, only hydrate if property has Property attribute
        if ($hasSkipAllProperties) {
            return $hasProperty;
        }
        
        // Default behavior (KeepAllProperties): hydrate unless SkipProperty
        return true;
    }
}
