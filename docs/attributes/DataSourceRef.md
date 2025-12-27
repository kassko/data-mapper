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
| `candidates` | `?array` | Conditional | Array of candidates with discriminator expressions |
| `exceptionOnNoValidDataSource` | `?string` | No | Exception class for fallback handling |
| `priority` | `int` | No | Hydration priority (default: 0) |

**Validation Rules:**
- `id`, `providers`, and `candidates` are mutually exclusive
- `fallbacks` can only be used with `id`
- `exceptionOnNoValidDataSource` requires `fallbacks` to be set
- Each candidate must have `id` and `discriminator` keys

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

### Candidates (Discriminator-Based Selection)

Select a data source dynamically based on expression evaluation. The first candidate whose `discriminator` evaluates to `true` is elected:

```php
#[DataSourcesStore([
    new MultiPropDataSource(id: 'newFeatureSource', class: NewFeatureSource::class, method: 'getData'),
    new MultiPropDataSource(id: 'oldFeatureSource', class: OldFeatureSource::class, method: 'getData'),
])]
class User
{
    #[DataSourceRef(
        candidates: [
            ['id' => 'newFeatureSource', 'discriminator' => "expr(context('new_feature_enabled'))", 'priority' => 15],
            ['id' => 'oldFeatureSource', 'discriminator' => 'expr(true)'],
        ],
        priority: 10
    )]
    private ?string $name = null;
}
```

**Candidate Structure:**
- `id` (required): The data source ID to use if this candidate is elected
- `discriminator` (required): An expression that returns a boolean
- `priority` (optional): Overrides the base `priority` if this candidate is elected

**Priority Resolution:**
If an elected candidate defines its own `priority`, it takes precedence over the base `priority` defined at the attribute level.

**Lineage Collection:**
When lineage collection is enabled, candidate resolution events are recorded with:
- All candidates that were evaluated
- Which candidate was elected (or none)
- Base priority vs effective priority used

## See Also

- [DataSource](DataSource.md)
- [SinglePropDataSource](SinglePropDataSource.md)
- [MultiPropDataSource](MultiPropDataSource.md)
- [DataSourcesStore](DataSourcesStore.md)

