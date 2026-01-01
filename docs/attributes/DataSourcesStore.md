# DataSourcesStore

Stores multiple data sources at the class level for reference by properties.

## Usage

```php
use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\Attribute\SinglePropDataSource;
use Kassko\DataMapper\Attribute\MultiPropDataSource;
use Kassko\DataMapper\Attribute\DataSourceRef;

#[DataSourcesStore([
    new SinglePropDataSource(
        id: 'nameSource',
        class: NameRepository::class,
        method: 'findName'
    ),
    new MultiPropDataSource(
        id: 'personData',
        class: PersonRepository::class,
        method: 'getAll',
        args: ['#id']
    ),
])]
class User
{
    private int $id;
    
    #[DataSourceRef(id: 'nameSource')]
    private ?string $name = null;
    
    #[DataSourceRef(id: 'personData')]
    private ?string $firstName = null;
    
    #[DataSourceRef(id: 'personData')]
    private ?string $lastName = null;
}
```

## Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `sources` | `array` | Yes | Array of SinglePropDataSource, DataSource, or MultiPropDataSource instances |

## Accepted Data Source Types

- `SinglePropDataSource` - For single-property data sources
- `DataSource` - Legacy single-property data sources
- `MultiPropDataSource` - For multi-property data sources (**NEW**)

**Important**: `MultiPropDataSource` can ONLY be used inside `DataSourcesStore`. It cannot be used as a standalone attribute.

## Important Notes

- All sources must have unique `id` values for referencing
- `MultiPropDataSource` is **not** repeatable and must be inside `DataSourcesStore`
- `SinglePropDataSource` is **not** repeatable

## When to Use

Use `DataSourcesStore` when:
- You have many data sources to organize
- Sources are reused across multiple properties
- You want to keep class definition cleaner
- You need to use `MultiPropDataSource` (required)

## Example with Multiple Types

```php
#[DataSourcesStore([
    new SinglePropDataSource(id: 'avatar', class: AvatarService::class, method: 'get'),
    new MultiPropDataSource(id: 'personData', class: PersonSource::class, method: 'getAll', args: ['#id']),
])]
class Person
{
    #[DataSourceRef(id: 'personData')]
    private ?string $firstName = null;
    
    #[DataSourceRef(id: 'avatar')]
    private ?string $avatar = null;
}
```

## Attribute Cascading

`DataSourcesStore` supports cascading from parent classes and traits. Child classes can reference DataSources defined in:

- Parent class `DataSourcesStore`
- Trait `DataSourcesStore`
- Their own `DataSourcesStore`

When the same ID is defined in multiple places, the child's definition wins.

```php
// Parent defines sources
#[DataSourcesStore([
    new SinglePropDataSource(id: 'parentSource', class: ParentService::class, method: 'get'),
])]
abstract class BaseEntity {}

// Child can reference parent's sources
class ChildEntity extends BaseEntity
{
    #[DataSourceRef(id: 'parentSource')] // Works!
    private ?string $data = null;
}
```

See [Attribute Cascading](AttributeCascading.md) for full details.

## See Also

- [DataSource](DataSource.md)
- [SinglePropDataSource](SinglePropDataSource.md)
- [MultiPropDataSource](MultiPropDataSource.md)
- [DataSourceRef](DataSourceRef.md)
- [Attribute Cascading](AttributeCascading.md)
