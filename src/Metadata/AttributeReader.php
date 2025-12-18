<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Metadata;

use Kassko\DataMapper\Attribute\DataSource;
use Kassko\DataMapper\Attribute\DataSourceRef;
use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\Attribute\Field;
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
     * Read Field attribute from a property
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
     * Build a map of DataSource id => DataSource from DataSourcesStore
     *
     * @param object $object
     * @return array<string, DataSource>
     */
    public function getDataSourceMap(object $object): array
    {
        $reflectionClass = new ReflectionClass($object);
        $store = $this->readDataSourcesStore($reflectionClass);
        
        if ($store === null) {
            return [];
        }
        
        $map = [];
        foreach ($store->sources as $source) {
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
}
