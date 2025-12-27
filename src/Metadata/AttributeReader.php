<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Metadata;

use Kassko\DataMapper\Attribute\Context;
use Kassko\DataMapper\Attribute\DataSource;
use Kassko\DataMapper\Attribute\DataSourceRef;
use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\Attribute\Getter;
use Kassko\DataMapper\Attribute\PropertySettingHook;
use Kassko\DataMapper\Attribute\PropertyInstantiatingHook;
use Kassko\DataMapper\Attribute\PropertyHydratingHook;
use Kassko\DataMapper\Attribute\CustomHydrator;
use Kassko\DataMapper\Attribute\Property;
use Kassko\DataMapper\Attribute\PropertyCandidates;
use Kassko\DataMapper\Attribute\KeepProperty;
use Kassko\DataMapper\Attribute\Loading;
use Kassko\DataMapper\Attribute\Needs;
use Kassko\DataMapper\Attribute\Setter;
use Kassko\DataMapper\Attribute\SkipProperty;
use Kassko\DataMapper\Attribute\SkipAllProperties;
use Kassko\DataMapper\Attribute\KeepAllProperties;
use Kassko\DataMapper\Attribute\SinglePropDataSource;
use Kassko\DataMapper\Attribute\MultiPropDataSource;
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
     * Read SinglePropDataSource or DataSource attribute from a property
     *
     * @param ReflectionProperty $property
     * @return SinglePropDataSource|DataSource|null
     */
    public function readSinglePropDataSource(ReflectionProperty $property): SinglePropDataSource|DataSource|null
    {
        // Try SinglePropDataSource first
        $attrs = $property->getAttributes(SinglePropDataSource::class);
        if (!empty($attrs)) {
            return $attrs[0]->newInstance();
        }
        
        // Then try DataSource alias
        $attrs = $property->getAttributes(DataSource::class);
        if (!empty($attrs)) {
            return $attrs[0]->newInstance();
        }
        
        return null;
    }

    /**
     * Read SinglePropDataSource or DataSource attribute from a property
     *
     * @param ReflectionProperty $property
     * @return SinglePropDataSource|DataSource|null
     */
    public function readMultiPropDataSource(ReflectionProperty $property): MultiPropDataSource|null
    {
        $attrs = $property->getAttributes(MultiPropDataSource::class);
        if (!empty($attrs)) {
            return $attrs[0]->newInstance();
        }

        return null;
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
     * Read Context attribute from a property
     *
     * @param ReflectionProperty $property
     * @return Context|null
     */
    public function readContext(ReflectionProperty $property): ?Context
    {
        $attributes = $property->getAttributes(Context::class);
        
        if (empty($attributes)) {
            return null;
        }
        
        return $attributes[0]->newInstance();
    }

    /**
     * Read all Context attributes from a property (supports IS_REPEATABLE)
     *
     * @param ReflectionProperty $property
     * @return Context[]
     */
    public function readAllContexts(ReflectionProperty $property): array
    {
        $attributes = $property->getAttributes(Context::class);
        
        if (empty($attributes)) {
            return [];
        }
        
        return array_map(fn($attr) => $attr->newInstance(), $attributes);
    }

    /**
     * Read Getter attribute from a property
     *
     * @param ReflectionProperty $property
     * @return Getter|null
     */
    public function readGetter(ReflectionProperty $property): ?Getter
    {
        $attributes = $property->getAttributes(Getter::class);
        
        if (empty($attributes)) {
            return null;
        }
        
        return $attributes[0]->newInstance();
    }

    /**
     * Read Setter attribute from a property
     *
     * @param ReflectionProperty $property
     * @return Setter|null
     */
    public function readSetter(ReflectionProperty $property): ?Setter
    {
        $attributes = $property->getAttributes(Setter::class);
        
        if (empty($attributes)) {
            return null;
        }
        
        return $attributes[0]->newInstance();
    }

    /**
     * Read KeepProperty attribute from a property
     *
     * @param ReflectionProperty $property
     * @return KeepProperty|null
     */
    public function readKeepProperty(ReflectionProperty $property): ?KeepProperty
    {
        $attributes = $property->getAttributes(KeepProperty::class);
        
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
     * Build a map of DataSource id => DataSource from DataSourcesStore
     *
     * @param object $object
     * @return array<string, DataSource|SinglePropDataSource|MultiPropDataSource>
     */
    public function getDataSourceMap(object $object): array
    {
        $reflectionClass = new ReflectionClass($object);
        $map = [];
        
        // Read from DataSourcesStore - the only valid way to define class-level data sources
        $store = $this->readDataSourcesStore($reflectionClass);
        if ($store !== null) {
            foreach ($store->sources as $source) {
                if ($source->id !== null) {
                    $map[$source->id] = $source;
                }
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
        $hasKeepProperty = $this->readKeepProperty($property) !== null;
        $hasSkipAllProperties = $this->hasSkipAllProperties($reflectionClass);
        
        // If property has SkipProperty, never hydrate
        if ($hasSkipProperty) {
            return false;
        }
        
        // If class has SkipAllProperties, only hydrate if property has Property or KeepProperty attribute
        if ($hasSkipAllProperties) {
            return $hasProperty || $hasKeepProperty;
        }
        
        // Default behavior (KeepAllProperties): hydrate unless SkipProperty
        return true;
    }

    /**
     * Read PropertyInstantiatingHook attributes from a class
     *
     * @param ReflectionClass $reflectionClass
     * @return PropertyInstantiatingHook[]
     */
    public function readPropertyInstantiatingHooks(ReflectionClass $reflectionClass): array
    {
        $attributes = $reflectionClass->getAttributes(PropertyInstantiatingHook::class);
        
        $hooks = [];
        foreach ($attributes as $attr) {
            $hooks[] = $attr->newInstance();
        }
        
        return $hooks;
    }

    /**
     * Read PropertyHydratingHook attributes from a class
     *
     * @param ReflectionClass $reflectionClass
     * @return PropertyHydratingHook[]
     */
    public function readPropertyHydratingHooks(ReflectionClass $reflectionClass): array
    {
        $attributes = $reflectionClass->getAttributes(PropertyHydratingHook::class);
        
        $hooks = [];
        foreach ($attributes as $attr) {
            $hooks[] = $attr->newInstance();
        }
        
        return $hooks;
    }

    /**
     * Read PropertySettingHook attributes from a property
     *
     * @param ReflectionProperty $property
     * @return PropertySettingHook[]
     */
    public function readPropertySettingHooks(ReflectionProperty $property): array
    {
        $attributes = $property->getAttributes(PropertySettingHook::class);
        
        $hooks = [];
        foreach ($attributes as $attr) {
            $hooks[] = $attr->newInstance();
        }
        
        return $hooks;
    }

    /**
     * Read CustomHydrator attribute from a property
     *
     * @param ReflectionProperty $property
     * @return CustomHydrator|null
     */
    public function readCustomHydrator(ReflectionProperty $property): ?CustomHydrator
    {
        $attributes = $property->getAttributes(CustomHydrator::class);
        
        if (empty($attributes)) {
            return null;
        }
        
        return $attributes[0]->newInstance();
    }

    /**
     * Read PropertyCandidates attribute from a property
     *
     * @param ReflectionProperty $property
     * @return PropertyCandidates|null
     */
    public function readPropertyCandidates(ReflectionProperty $property): ?PropertyCandidates
    {
        $attributes = $property->getAttributes(PropertyCandidates::class);
        
        if (empty($attributes)) {
            return null;
        }
        
        return $attributes[0]->newInstance();
    }

    /**
     * Read Needs attribute from a property
     *
     * @param ReflectionProperty $property
     * @return Needs|null
     */
    public function readNeeds(ReflectionProperty $property): ?Needs
    {
        $attributes = $property->getAttributes(Needs::class);
        
        if (empty($attributes)) {
            return null;
        }
        
        return $attributes[0]->newInstance();
    }
}
