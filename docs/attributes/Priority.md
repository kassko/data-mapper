# Priority-Based Hydration

**Available in:** v2.0+

All DataSource attributes support a `priority` field to control the order and precedence of property hydration.

## Concept

When multiple data sources can hydrate the same property, priority determines which source's value is used:

- **Higher priority** sources can override values from lower priority sources
- **Equal or lower priority** sources cannot override existing values
- **Default priority** is `0`

## Supported Attributes

All data source attributes support priority:

- `DataSource`
- `SinglePropDataSource`
- `MultiPropDataSource`
- `DataSourceRef`

## Usage

### Basic Priority

```php
use Kassko\DataMapper\Attribute\DataSource;

class Product
{
    // Load from cache first (low priority)
    #[DataSource(
        class: CacheService::class,
        method: 'getPrice',
        args: ['#id'],
        priority: 0  // Default, can be omitted
    )]
    private ?float $price = null;
    
    // Load from API (high priority - overrides cache)
    #[DataSource(
        class: ApiService::class,
        method: 'getCurrentPrice',
        args: ['#id'],
        priority: 10
    )]
    private ?float $price = null;
}
```

### With DataSourceRef

```php
use Kassko\DataMapper\Attribute\DataSourceRef;
use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\Attribute\MultiPropDataSource;

#[DataSourcesStore([
    new MultiPropDataSource(
        id: 'cacheData',
        class: CacheService::class,
        method: 'getAll',
        args: ['#id']
    ),
    new MultiPropDataSource(
        id: 'freshData',
        class: DatabaseService::class,
        method: 'fetch',
        args: ['#id']
    ),
])]
class User
{
    private int $id;
    
    // Low priority - loads from cache
    #[DataSourceRef(id: 'cacheData', priority: 0)]
    private ?string $name = null;
    
    // High priority - overrides cache with fresh data
    #[DataSourceRef(id: 'freshData', priority: 5)]
    private ?string $name = null;
}
```

### With Fallbacks

Priority applies to the entire fallback chain:

```php
#[DataSourceRef(
    id: 'primaryApi',
    fallbacks: ['backup', 'default'],
    priority: 10  // This priority applies to the whole fallback chain
)]
private ?string $data = null;
```

## Hydration Logic

When hydrating a property:

1. **Check if property already hydrated** from another source
2. **Compare priorities:**
   - If incoming priority > existing priority → **Override** the value
   - If incoming priority ≤ existing priority → **Skip** (keep existing value)
3. **Register the hydration** with source info and priority

## Use Cases

### Cache-then-Database Pattern

```php
// Try cache first (fast but might be stale)
#[DataSource(class: Cache::class, method: 'get', priority: 0)]
private ?array $data = null;

// Then try database (slower but authoritative)
#[DataSource(class: Database::class, method: 'fetch', priority: 5)]
private ?array $data = null;
```

### Default-then-Override Pattern

```php
// Load defaults
#[DataSourceRef(id: 'systemDefaults', priority: 0)]
private ?array $config = null;

// Override with user preferences
#[DataSourceRef(id: 'userPreferences', priority: 5)]
private ?array $config = null;

// Override with runtime settings
#[DataSourceRef(id: 'runtimeConfig', priority: 10)]
private ?array $config = null;
```

### Conditional Hydration

```php
// Always load basic data
#[DataSourceRef(id: 'basicInfo', priority: 1)]
private ?string $name = null;

// Optionally load premium data (higher priority)
#[DataSourceRef(id: 'premiumInfo', priority: 10)]
private ?string $name = null;
```

If `premiumInfo` source is loaded, it will override `basicInfo`. If it's not loaded (e.g., user not premium), `basicInfo` remains.

## Priority Tracking

The Loader tracks which source last hydrated each property:

```php
// Internal tracking (not exposed in public API)
[
    'propertyName' => [
        'source' => 'ClassName::methodName[id]',
        'priority' => 10
    ]
]
```

This allows the Loader to make informed decisions about overriding values.

## Best Practices

### Priority Ranges

Organize your priorities in ranges for clarity:

- **0-10**: Cache and fast sources
- **10-50**: Standard data sources
- **50-100**: Authoritative sources
- **100+**: Critical overrides

### Logging

Use a PSR-3 logger to track priority decisions:

```php
$builder = new DataMapperBuilder();
$builder->setLogger($logger);
$mapper = $builder->build();
```

The Loader logs when properties are skipped due to priority at INFO level.

### Avoid Conflicts

If two sources have the same priority, the order is undefined. Use distinct priorities when order matters.

## See Also

- [DataSource](DataSource.md)
- [DataSourceRef](DataSourceRef.md)
- [MultiPropDataSource](MultiPropDataSource.md)
