# SinglePropDataSource

Defines a data source that hydrates a single property.

## Usage

```php
use Kassko\DataMapper\Attribute\SinglePropDataSource;
use Kassko\DataMapper\Attribute\MultiPropDataSource;
use Kassko\DataMapper\Attribute\DataSourceRef;

#[MultiPropDataSource(id: 'fullData', class: PersonSource::class, method: 'getAll')]
class Person
{
    // Single property hydration
    #[SinglePropDataSource(class: AvatarService::class, method: 'getAvatar', args: ['#id'])]
    private ?string $avatar = null;
    
    // Multi property hydration via ref
    #[DataSourceRef(id: 'fullData')]
    private ?string $firstName = null;
}
```

## Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | `?string` | No | Unique identifier for referencing via DataSourceRef |
| `class` | `?string` | No | Service class to call |
| `method` | `string` | No | Method to call on the service |
| `args` | `array` | No | Arguments to pass to the method |

## Difference with DataSource

`DataSource` is an alias for `SinglePropDataSource`. They are functionally identical.

Use `SinglePropDataSource` when you also use `MultiPropDataSource` in your project, for clearer naming:
- `SinglePropDataSource` + `MultiPropDataSource` ✅ Clear
- `DataSource` + `MultiPropDataSource` ⚠️ Less clear

## See Also

- [DataSource](DataSource.md)
- [MultiPropDataSource](MultiPropDataSource.md)
