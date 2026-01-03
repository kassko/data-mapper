# MultiPropDataSource

Defines a data source that hydrates multiple properties from an associative array.

**Note**: This attribute can be used in three ways:
1. Inside `DataSourcesStore` (with `id`)
2. On classes (with `id`, for shared data sources)
3. On properties (for property-specific multi-value hydration)

## Usage Patterns

### 1. Inside DataSourcesStore (Recommended for shared sources)

```php
use Kassko\DataMapper\Attribute\MultiPropDataSource;
use Kassko\DataMapper\Attribute\DataSourceRef;
use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\Attribute\Property;

#[DataSourcesStore([
    new MultiPropDataSource(
        id: 'personData',
        class: PersonRepository::class,
        method: 'findById',
        args: ['#id'],
        loadingScope: MultiPropDataSource::SCOPE_ONLY_PROPS,
        loadingScopeProps: ['firstName', 'lastName']
    ),
])]
class Person
{
    private int $id;
    
    #[DataSourceRef(id: 'personData')]
    #[Property(key: 'first_name')]
    private ?string $firstName = null;
    
    #[DataSourceRef(id: 'personData')]
    #[Property(key: 'last_name')]
    private ?string $lastName = null;
    
    #[DataSourceRef(id: 'personData')]
    private ?string $email = null;  // Excluded by loadingScopeProps
}
```

### 2. On Class (Alternative to DataSourcesStore)

```php
#[MultiPropDataSource(
    id: 'personData',
    class: PersonRepository::class,
    method: 'findById',
    args: ['#id']
)]
class Person
{
    private int $id;
    
    #[DataSourceRef(id: 'personData')]
    private ?string $firstName = null;
    
    #[DataSourceRef(id: 'personData')]
    private ?string $lastName = null;
}
```

### 3. On Property (New in v2.0)

```php
class User
{
    private int $id;
    
    #[MultiPropDataSource(
        class: UserDataSource::class,
        method: 'getUserData',
        args: ['#id']
    )]
    private ?array $userData = null;
}
```

## Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `id` | `?string` | No | `null` | Unique identifier for referencing via DataSourceRef |
| `class` | `?string` | No | `null` | Service class to call |
| `method` | `string` | No | `''` | Method to call on the service |
| `args` | `array` | No | `[]` | Arguments to pass to the method |
| `priority` | `int` | No | `0` | Priority for hydration precedence. Higher values override lower values. See [Priority](Priority.md) |
| `loadingScope` | `string` | No | `'all'` | How to filter properties |
| `loadingScopeKeys` | `array` | No | `[]` | Raw data keys to include/exclude |
| `loadingScopeProps` | `array` | No | `[]` | Property names to include/exclude |

## Loading Scope

| Value | Description |
|-------|-------------|
| `all` | Hydrate all eligible properties |
| `only_keys` | Only hydrate properties matching keys in `loadingScopeKeys` |
| `except_keys` | Hydrate all except properties matching keys in `loadingScopeKeys` |
| `only_props` | Only hydrate properties listed in `loadingScopeProps` |
| `except_props` | Hydrate all except properties listed in `loadingScopeProps` |

## Scope Rules

**Target:** Can be used on:
- Inside `DataSourcesStore` (as value object)
- On classes (with `Attribute::TARGET_CLASS` and `IS_REPEATABLE`)
- On properties (with `Attribute::TARGET_PROPERTY`)

**Repeatability:** Yes - multiple instances can be applied to a class

```php
// ✅ CORRECT - Inside DataSourcesStore
#[DataSourcesStore([
    new MultiPropDataSource(id: 'source1', ...),
    new MultiPropDataSource(id: 'source2', ...),
])]
class Person { }

// ✅ CORRECT - On class
#[MultiPropDataSource(id: 'source1', ...)]
#[MultiPropDataSource(id: 'source2', ...)]
class Person { }

// ✅ CORRECT - On property
class Person {
    #[MultiPropDataSource(class: DataSource::class, method: 'getData')]
    private $data;
}
```

## Priority Usage with Scope

```php
use Kassko\DataMapper\Attribute\MultiPropDataSource;
use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\Attribute\DataSourceRef;

#[DataSourcesStore([
    // Load defaults for all properties
    new MultiPropDataSource(
        id: 'defaults',
        class: DefaultsService::class,
        method: 'getDefaults',
        priority: 0
    ),
    // Override only specific properties with database values
    new MultiPropDataSource(
        id: 'database',
        class: DatabaseService::class,
        method: 'findUser',
        args: ['#id'],
        loadingScope: MultiPropDataSource::SCOPE_ONLY_PROPS,
        loadingScopeProps: ['firstName', 'lastName'],
        priority: 10
    ),
])]
class User
{
    private int $id;
    
    #[DataSourceRef(id: 'defaults')]
    private ?string $firstName = null;  // Will be overridden by database
    
    #[DataSourceRef(id: 'defaults')]
    private ?string $lastName = null;   // Will be overridden by database
    
    #[DataSourceRef(id: 'defaults')]
    private ?string $email = null;       // Keeps default (not in database scope)
}
```

The combination of `priority` and `loadingScope` allows sophisticated hydration strategies where some properties use defaults while others are loaded from authoritative sources.

## See Also

- [DataSourcesStore](DataSourcesStore.md)
- [SinglePropDataSource](SinglePropDataSource.md)
- [DataSource](DataSource.md)
- [DataSourceRef](DataSourceRef.md)
- [Priority](Priority.md) - Detailed guide to priority-based hydration
