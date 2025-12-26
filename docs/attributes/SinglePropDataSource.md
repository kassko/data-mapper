# SinglePropDataSource

Defines a data source that hydrates a single property.

## Usage

```php
use Kassko\DataMapper\Attribute\SinglePropDataSource;
use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\Attribute\MultiPropDataSource;
use Kassko\DataMapper\Attribute\DataSourceRef;

#[DataSourcesStore([
    new MultiPropDataSource(id: 'fullData', class: PersonSource::class, method: 'getAll'),
])]
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
| `priority` | `int` | No | Priority for hydration precedence (default: 0). Higher values override lower values. See [Priority](Priority.md) |

## Difference with DataSource

`DataSource` is an alias for `SinglePropDataSource`. They are functionally identical.

Use `SinglePropDataSource` when you also use `MultiPropDataSource` in your project, for clearer naming:
- `SinglePropDataSource` + `MultiPropDataSource` ✅ Clear
- `DataSource` + `MultiPropDataSource` ⚠️ Less clear

## Priority Usage

```php
use Kassko\DataMapper\Attribute\SinglePropDataSource;
use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\Attribute\DataSourceRef;

#[DataSourcesStore([
    new SinglePropDataSource(
        id: 'defaultConfig',
        class: DefaultsService::class,
        method: 'getConfig',
        priority: 0
    ),
    new SinglePropDataSource(
        id: 'userConfig',
        class: UserService::class,
        method: 'getConfig',
        args: ['#userId'],
        priority: 10
    ),
])]
class Config
{
    private int $userId;
    
    #[DataSourceRef(id: 'defaultConfig')]
    private ?array $settings = null;
    
    #[DataSourceRef(id: 'userConfig')]
    private ?array $settings = null;
}
```

When loading, defaults are applied first, then user-specific settings override them based on priority.

## See Also

- [DataSource](DataSource.md)
- [MultiPropDataSource](MultiPropDataSource.md)
- [Priority](Priority.md) - Detailed guide to priority-based hydration
