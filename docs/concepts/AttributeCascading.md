# Attribute Cascading

DataMapper supports cascading PHP 8 attributes from parent classes and traits to child classes. This allows you to define reusable `DataSourcesStore` and `PropertyConfigStore` configurations that can be inherited and overridden.

## Overview

When hydrating an object, DataMapper will:

1. **Collect attributes from the full class hierarchy**: parent classes, traits, and the current class
2. **Merge `DataSourcesStore`** configurations from all sources
3. **Merge `PropertyConfigStore`** configurations from all sources
4. **Handle conflicts**: when the same ID exists in multiple places, the child's definition wins
5. **Log and collect** cascading events for debugging

## DataSourcesStore Cascading

### Basic Usage

```php
use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\Attribute\SinglePropDataSource;

// Parent class defines some data sources
#[DataSourcesStore([
    new SinglePropDataSource(id: 'parentSource', class: ParentDataSource::class, method: 'getData'),
])]
abstract class BaseEntity
{
    // ...
}

// Trait defines additional data sources
#[DataSourcesStore([
    new SinglePropDataSource(id: 'traitSource', class: TraitDataSource::class, method: 'getData'),
])]
trait DataSourceTrait
{
    // ...
}

// Child class can reference both parent and trait sources
#[DataSourcesStore([
    new SinglePropDataSource(id: 'childSource', class: ChildDataSource::class, method: 'getData'),
])]
class ChildEntity extends BaseEntity
{
    use DataSourceTrait;
    
    #[DataSourceRef(id: 'parentSource')] // Reference parent's source
    private ?string $parentData = null;
    
    #[DataSourceRef(id: 'traitSource')] // Reference trait's source
    private ?string $traitData = null;
    
    #[DataSourceRef(id: 'childSource')] // Reference own source
    private ?string $childData = null;
}
```

### ID Conflicts

When the same DataSource ID is defined in multiple places:

- **Child class wins** over parent class
- **Child class wins** over trait
- A **warning** is logged when an override occurs

```php
// Parent defines 'sharedSource'
#[DataSourcesStore([
    new SinglePropDataSource(id: 'sharedSource', class: ParentDataSource::class, method: 'getData'),
])]
abstract class BaseEntity {}

// Child also defines 'sharedSource' - this one will be used
#[DataSourcesStore([
    new SinglePropDataSource(id: 'sharedSource', class: ChildDataSource::class, method: 'getData'),
])]
class ChildEntity extends BaseEntity
{
    #[DataSourceRef(id: 'sharedSource')] // Uses ChildDataSource, not ParentDataSource
    private ?string $data = null;
}
```

## PropertyConfigStore Cascading

The same cascading rules apply to `PropertyConfigStore`:

```php
use Kassko\DataMapper\Attribute\PropertyConfig;
use Kassko\DataMapper\Attribute\PropertyConfigStore;
use Kassko\DataMapper\Attribute\Property;

// Parent class defines configs
#[PropertyConfigStore([
    new PropertyConfig(id: 'parentConfig', class: ParentProduct::class),
])]
abstract class BaseContainer {}

// Trait defines additional configs
#[PropertyConfigStore([
    new PropertyConfig(id: 'traitConfig', class: TraitProduct::class),
])]
trait ConfigTrait {}

// Child can use configs from parent, trait, and self
#[PropertyConfigStore([
    new PropertyConfig(id: 'childConfig', class: ChildProduct::class),
])]
class ChildContainer extends BaseContainer
{
    use ConfigTrait;
    
    #[Property(
        configCandidates: [
            ['id' => 'childConfig', 'rule' => "expr(rawDataItemExists('childType'))"],
            ['id' => 'parentConfig', 'rule' => "expr(rawDataItemExists('parentType'))"],
            ['id' => 'traitConfig', 'rule' => "expr(rawDataItemExists('traitType'))"],
        ],
        defaultConfigCandidate: 'childConfig'
    )]
    private ?object $item = null;
}
```

## Property Attribute Override

When a child class shadows a parent's private property with its own property of the same name, the **child's attributes are used exclusively**:

```php
class ParentClass
{
    #[Property(name: 'parent_name')] // This mapping is ignored when shadowed
    private ?string $name = null;
}

class ChildClass extends ParentClass
{
    #[Property(name: 'child_name')] // This mapping is used
    private ?string $name = null;
}

// Hydrating ChildClass with ['child_name' => 'John'] will work
// Hydrating ChildClass with ['parent_name' => 'John'] will NOT work
```

A **warning** is logged when this shadowing occurs.

## Monitoring Cascade Events

DataMapper provides an `AttributeCascadeCollector` to track and debug cascading:

```php
use Kassko\DataMapper\DataCollector\AttributeCascadeCollector;
use Kassko\DataMapper\DataCollector\CascadeEvent;

$collector = new AttributeCascadeCollector();
$collector->enable();

// ... hydration operations ...

// Get summary
$summary = $collector->getSummary();
// Returns: [
//     'dataSourcesStoreMerges' => int,
//     'propertyConfigStoreMerges' => int,
//     'dataSourceIdConflicts' => int,
//     'propertyConfigIdConflicts' => int,
//     'propertyAttributeOverrides' => int,
//     'total' => int,
// ]

// Get specific events
$conflicts = $collector->getEventsByType(CascadeEvent::TYPE_DATASOURCE_ID_CONFLICT);
foreach ($conflicts as $event) {
    echo "Conflict: {$event->metadata['dataSourceId']} in {$event->targetClass}";
}
```

## Event Types

| Event Type | Severity | Description |
|------------|----------|-------------|
| `TYPE_DATASOURCES_STORE_MERGE` | Info | DataSourcesStore merged from parent/trait |
| `TYPE_PROPERTY_CONFIG_STORE_MERGE` | Info | PropertyConfigStore merged from parent/trait |
| `TYPE_DATASOURCE_ID_CONFLICT` | Warning | DataSource ID overridden by child |
| `TYPE_PROPERTY_CONFIG_ID_CONFLICT` | Warning | PropertyConfig ID overridden by child |
| `TYPE_PROPERTY_ATTRIBUTE_OVERRIDE` | Warning | Property attributes shadowed by child |

## See Also

- [DataSourcesStore](DataSourcesStore.md)
- [PropertyConfigStore](PropertyConfigStore.md)
- [DataSourceRef](DataSourceRef.md)
- [Property](Property.md)
