# MultiPropDataSource

Defines a data source that hydrates multiple properties from an associative array.

**Important**: Each `MultiPropDataSource` id can only be explicitly referenced ONCE per class via `DataSourceRef`. Other properties are automatically hydrated via "cross-hydration" when the returned data contains keys matching their source field mappings.

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
        method: 'findById',  // Returns ['first_name' => 'John', 'last_name' => 'Doe', 'email' => 'john@example.com']
        args: ['#id'],
        loadingScope: MultiPropDataSource::SCOPE_ONLY_PROPS,
        loadingScopeProps: ['firstName', 'lastName']
    ),
])]
class Person
{
    private int $id;
    
    // Only ONE property references the MultiPropDataSource explicitly
    #[DataSourceRef(id: 'personData')]
    #[Property(sourceField: 'first_name')]
    private ?string $firstName = null;
    
    // Cross-hydrated: no DataSourceRef, but mapped via sourceField
    #[Property(sourceField: 'last_name')]
    private ?string $lastName = null;
    
    // Not hydrated: excluded by loadingScopeProps
    private ?string $email = null;
}
```

### 2. On Class (Alternative to DataSourcesStore)

```php
#[MultiPropDataSource(
    id: 'personData',
    class: PersonRepository::class,
    method: 'findById',  // Returns ['firstName' => 'John', 'lastName' => 'Doe']
    args: ['#id']
)]
class Person
{
    private int $id;
    
    // Only ONE property references the MultiPropDataSource
    #[DataSourceRef(id: 'personData')]
    private ?string $firstName = null;
    
    // Cross-hydrated automatically from 'lastName' key
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

The `priority` parameter controls which data source wins when multiple sources can hydrate the same property. Higher priority values take precedence. Properties with their own explicit `DataSourceRef` take precedence over cross-hydrated values at equal priority.

```php
use Kassko\DataMapper\Attribute\MultiPropDataSource;
use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\Attribute\DataSourceRef;

#[DataSourcesStore([
    // Low priority: Load defaults for all properties
    new MultiPropDataSource(
        id: 'defaults',
        class: DefaultsService::class,
        method: 'getDefaults',  // Returns ['firstName' => 'Default', 'lastName' => 'User', 'email' => 'default@example.com']
        priority: 0
    ),
    // High priority: Override specific properties with database values
    new MultiPropDataSource(
        id: 'database',
        class: DatabaseService::class,
        method: 'findUser',  // Returns ['firstName' => 'John', 'lastName' => 'Doe']
        args: ['#id'],
        loadingScope: MultiPropDataSource::SCOPE_ONLY_PROPS,
        loadingScopeProps: ['firstName', 'lastName'],
        priority: 10
    ),
])]
class User
{
    private int $id;
    
    // Only ONE reference per MultiPropDataSource id
    #[DataSourceRef(id: 'defaults')]
    private ?string $email = null;       // Hydrated from 'defaults' (priority 0)
    
    #[DataSourceRef(id: 'database')]
    private ?string $firstName = null;   // Hydrated from 'database' (priority 10)
    
    // Cross-hydrated from 'database' due to higher priority
    private ?string $lastName = null;
}
```

The combination of `priority` and `loadingScope` allows sophisticated hydration strategies where some properties use defaults while others are loaded from authoritative sources.

## See Also

- [DataSourcesStore](DataSourcesStore.md)
- [SinglePropDataSource](SinglePropDataSource.md)
- [DataSource](DataSource.md)
- [DataSourceRef](DataSourceRef.md)
- [Priority](Priority.md) - Detailed guide to priority-based hydration
