# MultiPropDataSource

Defines a data source that hydrates multiple properties from an associative array.

**IMPORTANT**: This attribute can ONLY be used inside `DataSourcesStore`. It cannot be used as a standalone attribute on classes.

## Usage

```php
use Kassko\DataMapper\Attribute\MultiPropDataSource;
use Kassko\DataMapper\Attribute\DataSourceRef;
use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\Attribute\Property;

#[DataSourcesStore([
    new MultiPropDataSource(
        id: 'personData',
        class: PersonRepository::class,
        method: 'findById',
        args: ['#id'],
        loadingScope: MultiPropDataSource::SCOPE_ONLY_PROPS,
        loadingScopeProps: ['firstName', 'lastName']
    ),
])]
class Person
{
    private int $id;
    
    #[DataSourceRef(id: 'personData')]
    #[Property(name: 'first_name')]
    private ?string $firstName = null;
    
    #[DataSourceRef(id: 'personData')]
    #[Property(name: 'last_name')]
    private ?string $lastName = null;
    
    #[DataSourceRef(id: 'personData')]
    private ?string $email = null;  // Excluded by loadingScopeProps
}
```

## Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `id` | `?string` | No | `null` | Unique identifier for referencing via DataSourceRef |
| `class` | `?string` | No | `null` | Service class to call |
| `method` | `string` | No | `''` | Method to call on the service |
| `args` | `array` | No | `[]` | Arguments to pass to the method |
| `loadingScope` | `string` | No | `'all'` | How to filter properties |
| `loadingScopeKeys` | `array` | No | `[]` | Raw data keys to include/exclude |
| `loadingScopeProps` | `array` | No | `[]` | Property names to include/exclude |

## Loading Scope

| Value | Description |
|-------|-------------|
| `all` | Hydrate all eligible properties |
| `only_keys` | Only hydrate properties matching keys in `loadingScopeKeys` |
| `except_keys` | Hydrate all except properties matching keys in `loadingScopeKeys` |
| `only_props` | Only hydrate properties listed in `loadingScopeProps` |
| `except_props` | Hydrate all except properties listed in `loadingScopeProps` |

## Scope Rules

- **ONLY** used inside `DataSourcesStore`
- **NOT** a standalone attribute (no `Attribute::TARGET_CLASS`)
- **NOT** repeatable
- Multiple `MultiPropDataSource` instances are grouped in a single `DataSourcesStore`

```php
// ✅ CORRECT
#[DataSourcesStore([
    new MultiPropDataSource(id: 'source1', ...),
    new MultiPropDataSource(id: 'source2', ...),
])]
class Person { }

// ❌ WRONG - Cannot use directly on class
#[MultiPropDataSource(id: 'source1', ...)]
class Person { }
```

## See Also

- [DataSourcesStore](DataSourcesStore.md)
- [SinglePropDataSource](SinglePropDataSource.md)
- [DataSource](DataSource.md)
- [DataSourceRef](DataSourceRef.md)
