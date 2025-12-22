# Needs

Declares property dependencies that must be loaded before this property.

## Usage

```php
use Kassko\DataMapper\Attribute\Needs;
use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\Attribute\MultiPropDataSource;
use Kassko\DataMapper\Attribute\DataSourceRef;

#[DataSourcesStore([
    new MultiPropDataSource(id: 'userData', class: UserSource::class, method: 'getData'),
    new MultiPropDataSource(id: 'fullName', class: NameService::class, method: 'buildFullName', args: ['#firstName', '#lastName']),
])]
class User
{
    #[DataSourceRef(id: 'userData')]
    private ?string $firstName = null;
    
    #[DataSourceRef(id: 'userData')]
    private ?string $lastName = null;
    
    #[Needs(['firstName', 'lastName'])]
    #[DataSourceRef(id: 'fullName')]
    private ?string $fullName = null;
}
```

## Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `properties` | `array` | Yes | Array of property names that must be loaded first |

## Behavior

- Automatically loads dependent properties before the annotated property
- Dependencies are loaded in the order specified
- Prevents circular dependencies
- Works with lazy loading

## See Also

- [DataSourceRef](DataSourceRef.md)
