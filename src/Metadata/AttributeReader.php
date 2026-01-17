<?php

declare(strict_types=1);

/*
 * This file is part of DataMapper.
 *
 * Copyright 2025 kassko 
 *
 * For the full copyright and license information,
 * please view the LICENSE and NOTICE files that were distributed with this source code.
 */

namespace Kassko\DataMapper\Metadata;

use Kassko\DataMapper\Attribute\Context;
use Kassko\DataMapper\Attribute\DataSource;
use Kassko\DataMapper\Attribute\DataSourceRef;
use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\Attribute\Getter;
use Kassko\DataMapper\Attribute\HandleAllProperties;
use Kassko\DataMapper\Attribute\HandleProperty;
use Kassko\DataMapper\Attribute\MethodAlias;
use Kassko\DataMapper\Attribute\PropertySettingHook;
use Kassko\DataMapper\Attribute\PropertyInstantiatingHook;
use Kassko\DataMapper\Attribute\PropertyHydratingHook;
use Kassko\DataMapper\Attribute\CustomHydrator;
use Kassko\DataMapper\Attribute\Property;
use Kassko\DataMapper\Attribute\PropertyConfig;
use Kassko\DataMapper\Attribute\PropertyConfigStore;
use Kassko\DataMapper\Attribute\Loading;
use Kassko\DataMapper\Attribute\MappingStrategy;
use Kassko\DataMapper\Attribute\Needs;
use Kassko\DataMapper\Attribute\RejectAttributeCascading;
use Kassko\DataMapper\Attribute\Setter;
use Kassko\DataMapper\Attribute\SinglePropDataSource;
use Kassko\DataMapper\Attribute\MultiPropDataSource;
use Kassko\DataMapper\DataCollector\AttributeCascadeCollector;
use ReflectionClass;
use ReflectionProperty;

class AttributeReader
{
    private ?AttributeCascadeCollector $cascadeCollector;

    public function __construct(?AttributeCascadeCollector $cascadeCollector = null)
    {
        $this->cascadeCollector = $cascadeCollector;
    }

    /**
     * Set the cascade collector for tracking attribute inheritance events.
     */
    public function setCascadeCollector(?AttributeCascadeCollector $cascadeCollector): void
    {
        $this->cascadeCollector = $cascadeCollector;
    }

    /**
     * Get the cascade collector.
     */
    public function getCascadeCollector(): ?AttributeCascadeCollector
    {
        return $this->cascadeCollector;
    }
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
        
        $instance = $attributes[0]->newInstance();
        return $instance->enabled ? $instance : null;
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
     * Read all Context attributes from a class (supports IS_REPEATABLE)
     *
     * @param ReflectionClass $reflectionClass
     * @return Context[]
     */
    public function readClassContexts(ReflectionClass $reflectionClass): array
    {
        $attributes = $reflectionClass->getAttributes(Context::class);
        
        if (empty($attributes)) {
            return [];
        }
        
        return array_map(fn($attr) => $attr->newInstance(), $attributes);
    }

    /**
     * Read all MethodAlias attributes from a class (supports IS_REPEATABLE)
     *
     * @param ReflectionClass $reflectionClass
     * @return MethodAlias[]
     */
    public function readMethodAliases(ReflectionClass $reflectionClass): array
    {
        $attributes = $reflectionClass->getAttributes(MethodAlias::class);
        
        if (empty($attributes)) {
            return [];
        }
        
        return array_map(fn($attr) => $attr->newInstance(), $attributes);
    }

    /**
     * Get MethodAlias map indexed by alias name (with cascading from parent classes)
     *
     * @param ReflectionClass $reflectionClass
     * @return array<string, MethodAlias>
     */
    public function getMethodAliasMap(ReflectionClass $reflectionClass): array
    {
        $allAliases = [];
        $classHierarchy = $this->getClassHierarchy($reflectionClass);
        
        // Process from top (oldest ancestor) to bottom (current class)
        // so that child classes override parent aliases with same name
        $classHierarchy = array_reverse($classHierarchy);
        
        foreach ($classHierarchy as $classInfo) {
            $class = $classInfo['class'];
            
            $aliases = $this->readMethodAliases($class);
            foreach ($aliases as $alias) {
                // Check cascade for parent classes
                if (!$classInfo['isCurrent'] && !$alias->cascade) {
                    continue;  // Skip non-cascading aliases from parent classes
                }
                $allAliases[$alias->name] = $alias;
            }
        }
        
        return $allAliases;
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
     * Read HandleProperty attribute from a property (skips disabled attributes)
     *
     * @param ReflectionProperty $property
     * @return HandleProperty|null
     */
    public function readHandleProperty(ReflectionProperty $property): ?HandleProperty
    {
        $attributes = $property->getAttributes(HandleProperty::class);
        
        if (empty($attributes)) {
            return null;
        }
        
        $instance = $attributes[0]->newInstance();
        return $instance->enabled ? $instance : null;
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
     * Read MappingStrategy attribute from a property (skips disabled attributes)
     *
     * @param ReflectionProperty $property
     * @return MappingStrategy|null
     */
    public function readMappingStrategyFromProperty(ReflectionProperty $property): ?MappingStrategy
    {
        $attributes = $property->getAttributes(MappingStrategy::class);
        
        if (empty($attributes)) {
            return null;
        }
        
        $instance = $attributes[0]->newInstance();
        return $instance->enabled ? $instance : null;
    }

    /**
     * Read MappingStrategy attribute from a class (skips disabled attributes)
     *
     * @param ReflectionClass $reflectionClass
     * @return MappingStrategy|null
     */
    public function readMappingStrategyFromClass(ReflectionClass $reflectionClass): ?MappingStrategy
    {
        $attributes = $reflectionClass->getAttributes(MappingStrategy::class);
        
        if (empty($attributes)) {
            return null;
        }
        
        $instance = $attributes[0]->newInstance();
        return $instance->enabled ? $instance : null;
    }

    /**
     * Get the effective MappingStrategy for a property.
     * 
     * Priority order (highest to lowest):
     * 1. Property-level MappingStrategy
     * 2. Class-level MappingStrategy
     * 3. null (use default strategy)
     *
     * @param ReflectionProperty $property
     * @param ReflectionClass $reflectionClass
     * @return MappingStrategy|null
     */
    public function getEffectiveMappingStrategy(ReflectionProperty $property, ReflectionClass $reflectionClass): ?MappingStrategy
    {
        // Property-level takes precedence
        $propertyStrategy = $this->readMappingStrategyFromProperty($property);
        if ($propertyStrategy !== null) {
            return $propertyStrategy;
        }

        // Fallback to class-level
        return $this->readMappingStrategyFromClass($reflectionClass);
    }

    /**
     * Read HandleAllProperties attribute from a class (skips disabled attributes)
     *
     * @param ReflectionClass $reflectionClass
     * @return HandleAllProperties|null
     */
    public function readHandleAllProperties(ReflectionClass $reflectionClass): ?HandleAllProperties
    {
        $attributes = $reflectionClass->getAttributes(HandleAllProperties::class);
        
        if (empty($attributes)) {
            return null;
        }
        
        $instance = $attributes[0]->newInstance();
        return $instance->enabled ? $instance : null;
    }

    /**
     * Check if class has HandleAllProperties(value: false) attribute
     *
     * @param ReflectionClass $reflectionClass
     * @return bool
     */
    public function hasHandleAllPropertiesFalse(ReflectionClass $reflectionClass): bool
    {
        $attr = $this->readHandleAllProperties($reflectionClass);
        return $attr !== null && $attr->value === false;
    }

    /**
     * Read RejectAttributeCascading attribute from a class
     *
     * @param ReflectionClass $reflectionClass
     * @return RejectAttributeCascading|null
     */
    public function readRejectAttributeCascading(ReflectionClass $reflectionClass): ?RejectAttributeCascading
    {
        $attributes = $reflectionClass->getAttributes(RejectAttributeCascading::class);
        
        if (empty($attributes)) {
            return null;
        }
        
        $instance = $attributes[0]->newInstance();
        return $instance->enabled ? $instance : null;
    }

    /**
     * Check if class has RejectAttributeCascading attribute
     *
     * @param ReflectionClass $reflectionClass
     * @return bool
     */
    public function hasRejectAttributeCascading(ReflectionClass $reflectionClass): bool
    {
        return $this->readRejectAttributeCascading($reflectionClass) !== null;
    }

    /**
     * Read DataSourcesStore attribute from a class (without cascading, skips disabled)
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
        
        $instance = $attributes[0]->newInstance();
        if (!$instance->enabled) {
            return null;
        }
        
        // Filter out disabled items
        $enabledItems = array_filter($instance->items, fn($item) => $item->enabled);
        
        // If all items were filtered out, return a store with empty items
        if (count($enabledItems) !== count($instance->items)) {
            return new DataSourcesStore(
                items: array_values($enabledItems),
                cascade: $instance->cascade,
                enabled: $instance->enabled
            );
        }
        
        return $instance;
    }

    /**
     * Read DataSourcesStore with full cascading from parent classes and traits.
     * 
     * Cascading rules:
     * - Merges DataSources from parent classes and traits
     * - Child class DataSources override parent/trait DataSources with same ID
     * - Logs info for merges, warning for ID conflicts
     * - RejectAttributeCascading stops cascading from ancestors
     *
     * @param ReflectionClass $reflectionClass
     * @return DataSourcesStore|null
     */
    public function readCascadedDataSourcesStore(ReflectionClass $reflectionClass): ?DataSourcesStore
    {
        $allSources = [];
        $targetClassName = $reflectionClass->getName();
        
        // Collect sources from class hierarchy (bottom-up: child first, then parents)
        $classHierarchy = $this->getClassHierarchy($reflectionClass);
        
        // Process from top (oldest ancestor) to bottom (current class)
        // so that child classes override parent sources with same ID
        $classHierarchy = array_reverse($classHierarchy);
        
        // Find if any class in hierarchy has RejectAttributeCascading
        $rejectCascadingAt = null;
        foreach ($classHierarchy as $index => $classInfo) {
            if ($this->hasRejectAttributeCascading($classInfo['class'])) {
                $rejectCascadingAt = $index;
            }
        }
        
        foreach ($classHierarchy as $index => $classInfo) {
            $class = $classInfo['class'];
            $isCurrentClass = $classInfo['isCurrent'];
            
            // Skip ancestors if RejectAttributeCascading is set on a descendant
            if ($rejectCascadingAt !== null && $index < $rejectCascadingAt) {
                continue;
            }
            
            // Read traits for this class
            $this->collectDataSourcesFromTraits($class, $targetClassName, $allSources);
            
            // Read direct class attribute
            $store = $this->readDataSourcesStore($class);
            if ($store !== null && $store->enabled) {
                foreach ($store->items as $source) {
                    // Skip disabled sources
                    if (!$source->enabled) {
                        continue;
                    }
                    if ($source->id !== null) {
                        // Check for ID conflict
                        if (isset($allSources[$source->id]) && !$isCurrentClass) {
                            $this->cascadeCollector?->recordDataSourceIdConflict(
                                $targetClassName,
                                $class->getName(),
                                'parent',
                                $source->id
                            );
                        }
                        $allSources[$source->id] = $source;
                    }
                }
                
                if (!$isCurrentClass && !empty($store->items)) {
                    $this->cascadeCollector?->recordDataSourcesStoreMerge(
                        $targetClassName,
                        $class->getName(),
                        'parent',
                        count($store->items)
                    );
                }
            }
        }
        
        if (empty($allSources)) {
            return null;
        }
        
        return new DataSourcesStore(items: array_values($allSources));
    }

    /**
     * Collect DataSources from traits (recursively).
     */
    private function collectDataSourcesFromTraits(
        ReflectionClass $class,
        string $targetClassName,
        array &$allSources
    ): void {
        foreach ($class->getTraits() as $trait) {
            // Recursively collect from traits used by this trait
            $this->collectDataSourcesFromTraits($trait, $targetClassName, $allSources);
            
            // Read DataSourcesStore from trait
            $traitStore = $this->readDataSourcesStore($trait);
            if ($traitStore !== null && $traitStore->enabled) {
                foreach ($traitStore->items as $source) {
                    // Skip disabled sources
                    if (!$source->enabled) {
                        continue;
                    }
                    if ($source->id !== null) {
                        // Check for ID conflict
                        if (isset($allSources[$source->id])) {
                            $this->cascadeCollector?->recordDataSourceIdConflict(
                                $targetClassName,
                                $trait->getName(),
                                'trait',
                                $source->id
                            );
                        }
                        $allSources[$source->id] = $source;
                    }
                }
                
                $this->cascadeCollector?->recordDataSourcesStoreMerge(
                    $targetClassName,
                    $trait->getName(),
                    'trait',
                    count($traitStore->items)
                );
            }
        }
    }

    /**
     * Build a map of DataSource id => DataSource from DataSourcesStore (with cascading)
     *
     * @param object $object
     * @return array<string, DataSource|SinglePropDataSource|MultiPropDataSource>
     */
    public function getDataSourceMap(object $object): array
    {
        $reflectionClass = new ReflectionClass($object);
        $map = [];
        
        // Read from cascaded DataSourcesStore - includes parent classes and traits
        $store = $this->readCascadedDataSourcesStore($reflectionClass);
        if ($store !== null) {
            foreach ($store->items as $source) {
                if ($source->id !== null && $source->enabled) {
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
     * Get all properties from a class including parent classes.
     * 
     * When a child class defines a property with the same name as a parent's private property,
     * the child's property (and its attributes) take precedence. This is logged as a warning.
     *
     * @param ReflectionClass $reflectionClass
     * @return array<string, ReflectionProperty>
     */
    public function getAllProperties(ReflectionClass $reflectionClass): array
    {
        $properties = [];
        $propertyOrigins = []; // Track where each property came from for conflict detection
        $targetClassName = $reflectionClass->getName();
        
        $class = $reflectionClass;
        while ($class !== false) {
            foreach ($class->getProperties() as $property) {
                $propName = $property->getName();
                
                // Don't override child properties with parent ones
                if (!isset($properties[$propName])) {
                    $properties[$propName] = $property;
                    $propertyOrigins[$propName] = $class->getName();
                } else {
                    // Property with same name exists in child - check if parent has attributes
                    if ($this->propertyHasAttributes($property) && $class->getName() !== $targetClassName) {
                        // Parent property has attributes but child shadows it
                        $this->cascadeCollector?->recordPropertyAttributeOverride(
                            $targetClassName,
                            $class->getName(),
                            $propName
                        );
                    }
                }
            }
            $class = $class->getParentClass();
        }
        
        return $properties;
    }

    /**
     * Check if a property has any DataMapper attributes.
     */
    private function propertyHasAttributes(ReflectionProperty $property): bool
    {
        $attributeClasses = [
            Property::class,
            DataSource::class,
            SinglePropDataSource::class,
            MultiPropDataSource::class,
            DataSourceRef::class,
            Context::class,
            Getter::class,
            Setter::class,
            Loading::class,
            Needs::class,
            HandleProperty::class,
            CustomHydrator::class,
            PropertySettingHook::class,
        ];
        
        foreach ($attributeClasses as $attrClass) {
            if (!empty($property->getAttributes($attrClass))) {
                return true;
            }
        }
        
        return false;
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
     * This method does NOT evaluate expressions in 'when' fields - that's the Loader's responsibility.
     * Use this for static checks only.
     *
     * @param ReflectionClass $reflectionClass
     * @param ReflectionProperty $property
     * @return bool|null Returns true/false for definitive answers, or null if expression evaluation is required
     */
    public function shouldHydrateProperty(ReflectionClass $reflectionClass, ReflectionProperty $property): bool
    {
        $handleProperty = $this->readHandleProperty($property);
        $propertyAttr = $this->readProperty($property);
        $handleAllPropertiesFalse = $this->hasHandleAllPropertiesFalse($reflectionClass);
        
        // If property has HandleProperty(value: false) without 'when' expression, never hydrate
        if ($handleProperty !== null && $handleProperty->value === false && $handleProperty->when === null) {
            return false;
        }
        
        // If property has HandleProperty with 'when' expression, defer to Loader
        // The Loader will properly evaluate and decide
        
        // If class has HandleAllProperties(value: false), only hydrate if property has Property or HandleProperty(value: true)
        if ($handleAllPropertiesFalse) {
            // If Property has handleWhen, defer to Loader for evaluation
            // If HandleProperty has when, defer to Loader for evaluation
            $hasPropertyWithoutHandleWhen = $propertyAttr !== null && $propertyAttr->handleWhen === null && $propertyAttr->enabled;
            $hasHandlePropertyTrueWithoutWhen = $handleProperty !== null && $handleProperty->value === true && $handleProperty->when === null;
            
            return $hasPropertyWithoutHandleWhen || $hasHandlePropertyTrueWithoutWhen;
        }
        
        // Default behavior (HandleAllProperties(value: true)): hydrate unless HandleProperty(value: false)
        return true;
    }

    /**
     * Get hydration decision info including expressions that need evaluation
     *
     * @param ReflectionClass $reflectionClass
     * @param ReflectionProperty $property
     * @return array{shouldHydrate: bool|null, handlePropertyWhen: ?string, handlePropertyValue: ?bool, propertyHandleWhen: ?string, hasHandleProperty: bool, hasProperty: bool, hasHandleAllPropertiesFalse: bool}
     */
    public function getHydrationDecisionInfo(ReflectionClass $reflectionClass, ReflectionProperty $property): array
    {
        $handleProperty = $this->readHandleProperty($property);
        $propertyAttr = $this->readProperty($property);
        $handleAllPropertiesFalse = $this->hasHandleAllPropertiesFalse($reflectionClass);
        
        return [
            'shouldHydrate' => null,  // Requires expression evaluation
            'handlePropertyWhen' => $handleProperty?->when,
            'handlePropertyValue' => $handleProperty?->value,
            'propertyHandleWhen' => $propertyAttr?->handleWhen,
            'hasHandleProperty' => $handleProperty !== null,
            'hasProperty' => $propertyAttr !== null && $propertyAttr->enabled,
            'hasHandleAllPropertiesFalse' => $handleAllPropertiesFalse,
        ];
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
     * Read PropertyConfigStore attribute from a class (without cascading, skips disabled)
     *
     * @param ReflectionClass $reflectionClass
     * @return PropertyConfigStore|null
     */
    public function readPropertyConfigStore(ReflectionClass $reflectionClass): ?PropertyConfigStore
    {
        $attributes = $reflectionClass->getAttributes(PropertyConfigStore::class);
        
        if (empty($attributes)) {
            return null;
        }
        
        $instance = $attributes[0]->newInstance();
        if (!$instance->enabled) {
            return null;
        }
        
        // Filter out disabled items
        $enabledItems = array_filter($instance->items, fn($item) => $item->enabled);
        
        // If all items were filtered out, return a store with empty items
        if (count($enabledItems) !== count($instance->items)) {
            return new PropertyConfigStore(
                items: array_values($enabledItems),
                cascade: $instance->cascade,
                enabled: $instance->enabled
            );
        }
        
        return $instance;
    }

    /**
     * Read PropertyConfigStore with full cascading from parent classes and traits.
     * 
     * Cascading rules:
     * - Merges PropertyConfigs from parent classes and traits
     * - Child class configs override parent/trait configs with same ID
     * - Logs info for merges, warning for ID conflicts
     * - RejectAttributeCascading stops cascading from ancestors
     *
     * @param ReflectionClass $reflectionClass
     * @return PropertyConfigStore|null
     */
    public function readCascadedPropertyConfigStore(ReflectionClass $reflectionClass): ?PropertyConfigStore
    {
        $allConfigs = [];
        $targetClassName = $reflectionClass->getName();
        
        // Collect configs from class hierarchy (bottom-up: child first, then parents)
        $classHierarchy = $this->getClassHierarchy($reflectionClass);
        
        // Process from top (oldest ancestor) to bottom (current class)
        // so that child classes override parent configs with same ID
        $classHierarchy = array_reverse($classHierarchy);
        
        // Find if any class in hierarchy has RejectAttributeCascading
        $rejectCascadingAt = null;
        foreach ($classHierarchy as $index => $classInfo) {
            if ($this->hasRejectAttributeCascading($classInfo['class'])) {
                $rejectCascadingAt = $index;
            }
        }
        
        foreach ($classHierarchy as $index => $classInfo) {
            $class = $classInfo['class'];
            $isCurrentClass = $classInfo['isCurrent'];
            
            // Skip ancestors if RejectAttributeCascading is set on a descendant
            if ($rejectCascadingAt !== null && $index < $rejectCascadingAt) {
                continue;
            }
            
            // Read traits for this class
            $this->collectPropertyConfigsFromTraits($class, $targetClassName, $allConfigs);
            
            // Read direct class attribute
            $store = $this->readPropertyConfigStore($class);
            if ($store !== null && $store->enabled) {
                foreach ($store->items as $id => $config) {
                    // Skip disabled configs
                    if (!$config->enabled) {
                        continue;
                    }
                    // Check for ID conflict
                    if (isset($allConfigs[$id]) && !$isCurrentClass) {
                        $this->cascadeCollector?->recordPropertyConfigIdConflict(
                            $targetClassName,
                            $class->getName(),
                            'parent',
                            $id
                        );
                    }
                    $allConfigs[$id] = $config;
                }
                
                if (!$isCurrentClass && !empty($store->items)) {
                    $this->cascadeCollector?->recordPropertyConfigStoreMerge(
                        $targetClassName,
                        $class->getName(),
                        'parent',
                        count($store->items)
                    );
                }
            }
        }
        
        if (empty($allConfigs)) {
            return null;
        }
        
        // Convert back to PropertyConfig array format for constructor
        return new PropertyConfigStore(items: array_values($allConfigs));
    }

    /**
     * Collect PropertyConfigs from traits (recursively).
     */
    private function collectPropertyConfigsFromTraits(
        ReflectionClass $class,
        string $targetClassName,
        array &$allConfigs
    ): void {
        foreach ($class->getTraits() as $trait) {
            // Recursively collect from traits used by this trait
            $this->collectPropertyConfigsFromTraits($trait, $targetClassName, $allConfigs);
            
            // Read PropertyConfigStore from trait
            $traitStore = $this->readPropertyConfigStore($trait);
            if ($traitStore !== null && $traitStore->enabled) {
                foreach ($traitStore->items as $id => $config) {
                    // Skip disabled configs
                    if (!$config->enabled) {
                        continue;
                    }
                    // Check for ID conflict
                    if (isset($allConfigs[$id])) {
                        $this->cascadeCollector?->recordPropertyConfigIdConflict(
                            $targetClassName,
                            $trait->getName(),
                            'trait',
                            $config->id
                        );
                    }
                    $allConfigs[$id] = $config;
                }
                
                $this->cascadeCollector?->recordPropertyConfigStoreMerge(
                    $targetClassName,
                    $trait->getName(),
                    'trait',
                    count($traitStore->items)
                );
            }
        }
    }

    /**
     * Get class hierarchy from current class up to root.
     * 
     * @param ReflectionClass $reflectionClass
     * @return array<array{class: ReflectionClass, isCurrent: bool}>
     */
    private function getClassHierarchy(ReflectionClass $reflectionClass): array
    {
        $hierarchy = [];
        $class = $reflectionClass;
        $isFirst = true;
        
        while ($class !== false) {
            $hierarchy[] = [
                'class' => $class,
                'isCurrent' => $isFirst,
            ];
            $isFirst = false;
            $class = $class->getParentClass();
        }
        
        return $hierarchy;
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
