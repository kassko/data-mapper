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

Lifecycle callbacks with support for external services:

```php
// Hook on the data object itself
#[Hook(name: 'after_create_object', method: 'initialize', args: ['##this'])]
class Entity
{
    #[Hook(name: 'before_set_property', method: 'validate', args: ["expr(rawDataItem('name'))"])]
    #[Hook(name: 'after_set_property', method: 'onSet', args: ['##this', '#name'])]
    private ?string $name = null;
}

// Hook on external service
#[Hook(
    name: 'after_set_property',
    class: ValidationService::class,  // External service
    method: 'validateEmail',
    args: ['##this', '#email']
)]
private ?string $email = null;
```

When `class` is provided, the hook calls the method on the external service instead of the data object.

#### Needs

Specify property dependencies that must be loaded before the current property.

**Important**: Properties passed as arguments (`#propX`) are automatically loaded and do NOT require `Needs`.

Use `Needs` when you need properties loaded for reasons OTHER than passing them as arguments:
- Properties used in the **getter** of the current property
- Properties needed for **validation** or **computation** after loading
- Properties used in **hooks** associated with the current property

```php
class Order
{
    #[DataSourceRef(id: 'customerSource')]
    private ?Customer $customer = null;
    
    #[DataSourceRef(id: 'discountSource')]
    private ?float $discount = null;
    
    #[DataSourceRef(id: 'orderIdSource')]
    private ?string $orderId = null;
    
    // orderId is in args -> auto-loads (NO Needs required)
    // customer and discount are NOT in args, but used in getter -> need Needs
    #[Needs(['customer', 'discount'])]
    #[DataSource(
        class: PriceCalculator::class,
        method: 'calculateTotal',
        args: ['#orderId']  // ← orderId auto-loads, no Needs required
    )]
    private ?float $finalPrice = null;
    
    public function getFinalPrice(): ?float
    {
        $this->loadProperty('finalPrice');
        
        // customer and discount were loaded via Needs
        if ($this->customer->isPremium()) {
            return $this->finalPrice * (1 - $this->discount);
        }
        return $this->finalPrice;
    }
}
```

Properties listed in `Needs` are loaded before the annotated property. Already-loaded properties are not reloaded.

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
| `#id` | Property value (tries getter → isser → haser → direct) |
| `!#id` | Property value (direct, bypass getter) |
| `##this` | Current object |

**Property Reference Resolution Order:**
1. `getPropertyName()` - Getter method
2. `isPropertyName()` - Isser method (for booleans)
3. `hasPropertyName()` - Haser method
4. Direct property access via reflection

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