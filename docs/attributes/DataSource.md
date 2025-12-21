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

## When to Use

- Use `DataSource` when your project only uses single-property hydration
- Use `SinglePropDataSource` when mixing with `MultiPropDataSource` for clearer code

## See Also

- [SinglePropDataSource](SinglePropDataSource.md)
- [MultiPropDataSource](MultiPropDataSource.md)
- [DataSourceRef](DataSourceRef.md)
