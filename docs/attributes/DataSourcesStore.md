# DataSourcesStore

Stores multiple data sources at the class level for reference by properties.

## Usage

```php
use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\Attribute\SinglePropDataSource;
use Kassko\DataMapper\Attribute\DataSourceRef;

#[DataSourcesStore([
    new SinglePropDataSource(
        id: 'nameSource',
        class: NameRepository::class,
        method: 'findName'
    ),
    new SinglePropDataSource(
        id: 'emailSource',
        class: EmailRepository::class,
        method: 'findEmail'
    )
])]
class User
{
    #[DataSourceRef(id: 'nameSource')]
    private ?string $name = null;
    
    #[DataSourceRef(id: 'emailSource')]
    private ?string $email = null;
}
```

## Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `sources` | `array` | Yes | Array of SinglePropDataSource or DataSource instances |

## Important Notes

- Only accepts `SinglePropDataSource` or `DataSource` instances
- **Cannot** contain `MultiPropDataSource` (use class-level attribute directly instead)
- All sources must have unique `id` values for referencing

## When to Use

Use `DataSourcesStore` when:
- You have many data sources to organize
- Sources are reused across multiple properties
- You want to keep class definition cleaner

## Alternative

Instead of `DataSourcesStore`, you can use:
- `#[SinglePropDataSource]` directly on properties
- `#[MultiPropDataSource]` directly on the class (repeatable)

## See Also

- [DataSource](DataSource.md)
- [SinglePropDataSource](SinglePropDataSource.md)
- [DataSourceRef](DataSourceRef.md)
