# DataSource

Alias for `SinglePropDataSource`. Defines a data source that hydrates a single property.

## Usage

```php
use Kassko\DataMapper\Attribute\DataSource;

class User
{
    #[DataSource(
        class: UserRepository::class,
        method: 'findName',
        args: ['#id']
    )]
    private ?string $name = null;
}
```

## Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | `?string` | No | Unique identifier for referencing via DataSourceRef |
| `class` | `?string` | No | Service class to call |
| `method` | `string` | No | Method to call on the service |
| `args` | `array` | No | Arguments to pass to the method |
| `priority` | `int` | No | Priority for hydration precedence (default: 0). Higher values override lower values. See [Priority](Priority.md) |

## When to Use

- Use `DataSource` when your project only uses single-property hydration
- Use `SinglePropDataSource` when mixing with `MultiPropDataSource` for clearer code

## Priority Usage

```php
use Kassko\DataMapper\Attribute\DataSource;

class Product
{
    private int $id = 1;
    
    // Load from cache first (low priority)
    #[DataSource(
        class: CacheService::class,
        method: 'getPrice',
        args: ['#id'],
        priority: 0
    )]
    private ?float $price = null;
    
    // Override with fresh data from API (higher priority)
    #[DataSource(
        class: ApiService::class,
        method: 'getCurrentPrice',
        args: ['#id'],
        priority: 10
    )]
    private ?float $price = null;
}
```

When `loadProperty('price')` is called, the cache value is loaded first, then immediately overridden by the API value because it has higher priority.

## See Also

- [SinglePropDataSource](SinglePropDataSource.md)
- [MultiPropDataSource](MultiPropDataSource.md)
- [DataSourceRef](DataSourceRef.md)
- [Priority](Priority.md) - Detailed guide to priority-based hydration
