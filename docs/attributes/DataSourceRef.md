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
| `candidates` | `?array` | Conditional | Array of candidates with rule expressions |
| `defaultCandidate` | `?array` | Conditional | Default candidate if no rule matches (required with `candidates`) |
| `exceptionOnNoValidFallback` | `?string` | No | Exception class for fallback handling |
| `ignoreProviderOnNotFound` | `bool` | No | Silently skip missing providers (requires `providers`, default: false) |
| `priority` | `int` | No | Hydration priority (default: 0) |

**Validation Rules:**
- `id`, `providers`, and `candidates` are mutually exclusive
- `candidates` and `defaultCandidate` must both be present or both absent
- `fallbacks` and `exceptionOnNoValidFallback` cannot be used with `candidates`
- `fallbacks` can only be used with `id`
- `exceptionOnNoValidFallback` requires `fallbacks` to be set
- `ignoreProviderOnNotFound` can only be used with `providers`
- Each candidate must have `id` and `rule` keys

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
    exceptionOnNoValidFallback: NoValidDataSourceException::class
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

#### Complete Providers Example

```php
use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\Attribute\MultiPropDataSource;
use Kassko\DataMapper\Attribute\DataSourceRef;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

#[DataSourcesStore([
    new MultiPropDataSource(id: 'basicInfo', class: UserBasicDataSource::class, method: 'getBasic', args: ['#userId']),
    new MultiPropDataSource(id: 'extendedInfo', class: UserExtendedDataSource::class, method: 'getExtended', args: ['#userId']),
    new MultiPropDataSource(id: 'preferences', class: UserPreferencesDataSource::class, method: 'getPrefs', args: ['#userId']),
])]
class UserProfile
{
    use LoadableTrait;
    
    private int $userId;
    
    /**
     * Aggregates data from all three sources.
     * Later providers override earlier ones for conflicting keys.
     */
    #[DataSourceRef(providers: ['basicInfo', 'extendedInfo', 'preferences'])]
    private ?array $userData = null;
    
    /**
     * With ignoreProviderOnNotFound: true, missing providers are silently skipped.
     * Useful when some providers may not be available in all environments.
     */
    #[DataSourceRef(
        providers: ['basicInfo', 'optionalProvider', 'preferences'],
        ignoreProviderOnNotFound: true
    )]
    private ?array $partialData = null;
    
    public function getUserData(): ?array
    {
        $this->loadProperty('userData');
        return $this->userData;
    }
}
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

### Candidates (Rule-Based Selection)

Select a data source dynamically based on expression evaluation. The first candidate whose `rule` evaluates to `true` is elected. If no rule matches, `defaultCandidate` is used:

```php
#[DataSourcesStore([
    new MultiPropDataSource(id: 'newFeatureSource', class: NewFeatureSource::class, method: 'getData'),
    new MultiPropDataSource(id: 'oldFeatureSource', class: OldFeatureSource::class, method: 'getData'),
])]
class User
{
    #[DataSourceRef(
        candidates: [
            ['id' => 'newFeatureSource', 'rule' => "expr(context('new_feature_enabled'))", 'priority' => 15],
        ],
        defaultCandidate: ['id' => 'oldFeatureSource'],
        priority: 10
    )]
    private ?string $name = null;
}
```

**Candidate Structure:**
- `id` (required): The data source ID to use if this candidate is elected
- `rule` (required): An expression that returns a boolean
- `priority` (optional): Overrides the base `priority` if this candidate is elected

**defaultCandidate Structure:**
- `id` (required): The data source ID to use when no rule matches
- `priority` (optional): Overrides the base `priority` for the default candidate

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

