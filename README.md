# Data Mapper 2

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

## Hydration Modes

The library supports two distinct hydration modes for clarity and type safety:

### Single-Property Hydration

Use `DataSource` or `SinglePropDataSource` to hydrate one property at a time:

```php
use Kassko\DataMapper\Attribute\SinglePropDataSource;

class User
{
    #[SinglePropDataSource(
        class: AvatarService::class,
        method: 'getAvatar',
        args: ['#id']
    )]
    private ?string $avatar = null;
}
```

### Multi-Property Hydration

Use `MultiPropDataSource` on the class to hydrate multiple properties from one data source:

```php
use Kassko\DataMapper\Attribute\MultiPropDataSource;
use Kassko\DataMapper\Attribute\DataSourceRef;

#[MultiPropDataSource(
    id: 'personData',
    class: PersonSource::class,
    method: 'getAll',
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

### Choosing Between Them

| Scenario | Recommendation |
|----------|----------------|
| Loading individual values | Use `DataSource` or `SinglePropDataSource` |
| Loading from associative array | Use `MultiPropDataSource` |
| Mix of both | Use `SinglePropDataSource` + `MultiPropDataSource` |

## Attributes

### DataSource

Alias for `SinglePropDataSource`. Best for projects using only single-property hydration.

See [docs/attributes/DataSource.md](docs/attributes/DataSource.md) for details.

### SinglePropDataSource & MultiPropDataSource

Explicit attributes for single vs. multi-property hydration modes.

- [SinglePropDataSource](docs/attributes/SinglePropDataSource.md)
- [MultiPropDataSource](docs/attributes/MultiPropDataSource.md)

### DataSourceRef

Reference DataSources with three modes:
- `id`: Single DataSource
- `chain`: Fallback chain (with `exception`)
- `providers`: Aggregation (merge results)

See [docs/attributes/DataSourceRef.md](docs/attributes/DataSourceRef.md) for details.

### Property

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

See [docs/attributes/Property.md](docs/attributes/Property.md) for details.

### Loading Scope

Control which properties to hydrate with `MultiPropDataSource`:

```php
#[MultiPropDataSource(
    id: 'personData',
    class: PersonSource::class,
    method: 'getData',
    loadingScope: MultiPropDataSource::SCOPE_ONLY_PROPS,
    loadingScopeProps: ['firstName', 'lastName']
)]
```

Available scopes: `SCOPE_ALL`, `SCOPE_ONLY_KEYS`, `SCOPE_EXCEPT_KEYS`, `SCOPE_ONLY_PROPS`, `SCOPE_EXCEPT_PROPS`

### Getter & Setter

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
#[Hook(name: 'after_create_object', method: 'initialize', args: ['##object'])]
class Entity
{
    #[Hook(name: 'before_set_property', method: 'validate', args: ["expr(rawDataItem('name'))"])]
    #[Hook(name: 'after_set_property', method: 'onSet', args: ['##object', '#name'])]
    private ?string $name = null;
}

// Hook on external service
#[Hook(
    name: 'after_set_property',
    class: ValidationService::class,  // External service
    method: 'validateEmail',
    args: ['##object', '#email']
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
| `##object` | Current object |
| `#parentObject` / `##parentObject` | Parent object |

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
| `object()` | Current object |
| `parentObject()` | Parent object |
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