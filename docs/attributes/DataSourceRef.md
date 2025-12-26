# DataSourceRef

References a data source by ID for lazy loading properties.

## v2.0 Changes

**BREAKING CHANGE:** The `chain` parameter has been replaced with `id` + `fallbacks` for clearer semantics and better validation.

**Migration:**
```php
// Before (v1.x)
#[DataSourceRef(chain: ['primary', 'backup'])]

// After (v2.0)
#[DataSourceRef(id: 'primary', fallbacks: ['backup'])]
```

## Usage

```php
use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\Attribute\MultiPropDataSource;
use Kassko\DataMapper\Attribute\DataSourceRef;

#[DataSourcesStore([
    new MultiPropDataSource(
        id: 'personData',
        class: PersonRepository::class,
        method: 'findById',
        args: ['#id']
    ),
])]
class Person
{
    private int $id;
    
    #[DataSourceRef(id: 'personData')]
    private ?string $firstName = null;
    
    #[DataSourceRef(id: 'personData')]
    private ?string $lastName = null;
}
```

## Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | `?string` | Conditional | Primary data source ID |
| `fallbacks` | `?array` | No | Array of fallback source IDs (requires `id`) |
| `providers` | `?array` | Conditional | Array of IDs for aggregation |
| `exceptionOnNoValidDataSource` | `?string` | No | Exception class for fallback handling |
| `priority` | `int` | No | Hydration priority (default: 0) |

**Validation Rules:**
- `id` and `providers` are mutually exclusive
- `fallbacks` can only be used with `id`
- `exceptionOnNoValidDataSource` requires `fallbacks` to be set

## Modes

### Simple Reference

```php
#[DataSourceRef(id: 'personData')]
private ?string $name = null;
```

### Fallback Pattern

Try primary source, then fallbacks in order until one succeeds:

```php
#[DataSourceRef(
    id: 'primaryApi',
    fallbacks: ['cacheBackup', 'defaultValues'],
    exceptionOnNoValidDataSource: NoValidDataSourceException::class
)]
private ?string $data = null;
```

The loader will:
1. Try `primaryApi`
2. If it throws `NoValidDataSourceException`, try `cacheBackup`
3. If that also throws the exception, try `defaultValues`
4. If all fail, throw `NoValidDataSourceException`

### Aggregation

Merge results from multiple sources (uses `array_replace_recursive`):

```php
#[DataSourceRef(providers: ['basicConfig', 'userPreferences', 'overrides'])]
private ?array $config = null;
```

### Priority-Based Hydration

Control which sources take precedence:

```php
// This will be loaded first (or can be overridden by higher priority)
#[DataSourceRef(id: 'cacheSource', priority: 0)]
private ?string $value = null;

// This will override the cached value if loaded
#[DataSourceRef(id: 'apiSource', priority: 10)]
private ?string $value = null;
```

## See Also

- [DataSource](DataSource.md)
- [SinglePropDataSource](SinglePropDataSource.md)
- [MultiPropDataSource](MultiPropDataSource.md)
- [DataSourcesStore](DataSourcesStore.md)

