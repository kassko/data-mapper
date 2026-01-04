# RejectAttributeCascading

Class-level attribute that prevents inheriting cascaded attributes from parent classes and traits.

## Usage

```php
use Kassko\DataMapper\Attribute\RejectAttributeCascading;
use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\Attribute\DataSource;

// Parent class with data sources
#[DataSourcesStore(items: [
    new DataSource(id: 'parentSource', class: ParentDataSource::class),
])]
class ParentEntity
{
    // ...
}

// Child class that REJECTS cascading from parent
#[RejectAttributeCascading]
#[DataSourcesStore(items: [
    new DataSource(id: 'childSource', class: ChildDataSource::class),
])]
class ChildEntity extends ParentEntity
{
    // This class only has 'childSource', NOT 'parentSource'
}
```

## Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `enabled` | `bool` | No | `true` | Whether this attribute is active (disabled attributes are ignored) |

## Behavior

When a class has `#[RejectAttributeCascading]`:

1. **DataSourcesStore**: Does not inherit DataSources from parent classes or traits
2. **PropertyConfigStore**: Does not inherit PropertyConfigs from parent classes or traits
3. **Other attributes**: May be affected depending on their cascading implementation

## Use Cases

### Isolating a Child Class

When you want a child class to have completely independent data source or property config definitions:

```php
#[DataSourcesStore(items: [
    new DataSource(id: 'legacySource', class: LegacyDataSource::class),
])]
class LegacyEntity
{
    // Old implementation with legacy data source
}

#[RejectAttributeCascading]  // Start fresh, don't use legacySource
#[DataSourcesStore(items: [
    new DataSource(id: 'modernSource', class: ModernDataSource::class),
])]
class ModernEntity extends LegacyEntity
{
    // New implementation with modern data source only
}
```

### Preventing Trait Pollution

When traits define data sources you don't want:

```php
trait SharedTrait
{
    // Trait has a DataSourcesStore you don't want to inherit
}

#[RejectAttributeCascading]  // Don't inherit trait's data sources
#[DataSourcesStore(items: [
    new DataSource(id: 'mySource', class: MyDataSource::class),
])]
class MyEntity
{
    use SharedTrait;
    // Only 'mySource' is available, not trait's sources
}
```

## Disabling RejectAttributeCascading

You can disable the attribute with `enabled: false`:

```php
#[RejectAttributeCascading(enabled: false)]  // Same as not having it
class Entity extends ParentEntity
{
    // Will inherit from parent normally
}
```

## See Also

- [DataSourcesStore](DataSourcesStore.md) - Data source collection with cascading
- [PropertyConfigStore](PropertyConfigStore.md) - Property config collection with cascading
