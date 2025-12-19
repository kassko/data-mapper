# Data Mapper

A powerful PHP 8.1+ library for mapping and hydrating data objects with lazy loading, expression language, and advanced features.

## Origin

This project was initiated as a personal open-source initiative, developed independently and outside of any professional assignment.

It is not affiliated with, nor owned by, any organization.

[Read more about the project background](./ABOUT.md)

## Requirements

- PHP >= 8.1
- Symfony ExpressionLanguage component

## Installation

```bash
composer require kassko/data-mapper-experimental
```

## Features

### Core Concepts

- **Lazy Loading**: Properties are loaded on-demand via DataSources
- **Expression Language**: Dynamic argument resolution
- **Lifecycle Hooks**: Callbacks during hydration
- **Polymorphic Hydration**: Runtime type resolution

### Attributes

#### DataSource & DataSourcesStore

Define data sources at class or property level:

```php
#[DataSourcesStore([
    new DataSource(
        id: 'personSource',
        class: PersonDataSource::class,
        method: 'getData',
        args: ['#id'],
        supplySeveralProperties: true
    )
])]
class Person
{
    use LoadableTrait;
    
    #[DataSourceRef(id: 'personSource')]
    #[Property(name: 'first_name')]
    private ?string $firstName = null;
}
```

#### DataSourceRef

Reference DataSources with three modes:
- `id`: Single DataSource
- `chain`: Fallback chain (with `exception`)
- `providers`: Aggregation (merge results)

```php
// Fallback chain
#[DataSourceRef(
    chain: ['sourceA', 'sourceB', 'sourceC'],
    exception: UnsuitableSourceException::class
)]

// Aggregation
#[DataSourceRef(
    providers: ['providerA', 'providerB', 'providerC']
)]
```

#### Property

Map and configure properties:

```php
#[Property(
    name: 'first_name',           // Key in data array
    class: Address::class,        // For nested objects
    mapping: ['src_key' => 'dest_key'],  // Instance mapping
    expand: 'field1,field2',      // Fields to expand
    noExpand: 'field3'            // Fields to skip
)]
```

#### Loading Scope

Control which properties to hydrate:

```php
#[DataSource(
    loadingScope: 'property',              // Only triggered property
    // or
    loadingScope: 'data_source_only_keys',
    loadingScopeKeys: ['first_name', 'last_name'],
    // or
    loadingScope: 'data_source_except_keys',
    loadingScopeKeys: ['phone']
)]
```

#### Getter & Setter

Custom property access:

```php
#[Getter(name: 'isEnabled', type: 'isser')]
#[Setter(name: 'setName', type: 'setter')]
private ?string $name = null;
```

#### Hook

Lifecycle callbacks:

```php
#[Hook(name: 'after_create_object', method: 'initialize', args: ['##this'])]
class Entity
{
    #[Hook(name: 'before_set_property', method: 'validate', args: ["expr(rawDataItem('name'))"])]
    #[Hook(name: 'after_set_property', method: 'onSet', args: ['##this', '#name'])]
    private ?string $name = null;
}
```

#### PropertyCandidates (Polymorphism)

Runtime type resolution:

```php
#[PropertyCandidates([
    new PropertyCandidate(
        discriminator: "expr(rawDataItemExists('gasoline_kind'))",
        property: new Property(class: GasolineCar::class)
    ),
    new PropertyCandidate(
        discriminator: "expr(rawDataItemExists('energy_provider'))",
        property: new Property(class: ElectricCar::class)
    )
])]
private array $cars = [];
```

#### KeepAllProperties, SkipAllProperties, SkipProperty, KeepProperty

Control property inclusion:

```php
#[SkipAllProperties]
class Person
{
    #[KeepProperty]  // Explicitly include
    private ?string $name = null;
    
    private ?string $internal = null;  // Excluded
}
```

#### Context

Set context variables:

```php
#[Context(['shop_quality' => 'premium'])]
private ?Shop $shop = null;
```

### Expression Language

#### Simple Expressions

| Syntax | Description |
|--------|-------------|
| `#id` | Property value (via getter) |
| `!#id` | Property value (direct, bypass getter) |
| `##this` | Current object |

#### Advanced Expressions (`expr()`)

| Function | Description |
|----------|-------------|
| `source('id')` | Get DataSource result |
| `service('id')` | Resolve service |
| `context('key')` | Get context value |
| `envVar('KEY')` | Environment variable |
| `_self()` | Current object |
| `rawDataItem('key')` | Raw data value |
| `rawDataItemExists('key')` | Check raw data key |
| `strictProperty('name')` | Direct property access |

## Testing

```bash
composer install
vendor/bin/phpunit
```

## License

Apache-2.0