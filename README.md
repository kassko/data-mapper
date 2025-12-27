# Data Mapper 2

A powerful PHP 8.1+ library for mapping and hydrating data objects with lazy loading, expression language, and advanced features.

## Origin

This project was initiated as a personal open-source initiative, developed independently and outside of any professional assignment.

It is not affiliated with, nor owned by, any organization.

[Read more about the project background](./ABOUT.md)

## Requirements

- PHP >= 8.1
- PSR-3 Logger Interface (optional, for logging)
- PSR-16 Simple Cache (optional, for caching)

## Installation

```bash
composer require kassko/data-mapper-experimental
```

## Quick Start

```php
use Kassko\DataMapper\DataMapperBuilder;

$builder = new DataMapperBuilder();

// Optional: Add a PSR-3 logger
$builder->setLogger($yourLogger);

// Optional: Add custom hydrators
$builder->addCustomHydrator('my_parser', function(array $data): ?object {
    return new MyClass($data);
});

$mapper = $builder->build();
```

## Features

### Core Concepts

- **Lazy Loading**: Properties are loaded on-demand via DataSources
- **Expression Language**: Dynamic argument resolution
- **Lifecycle Hooks**: Callbacks during hydration
- **Polymorphic Hydration**: Runtime type resolution
- **Priority-Based Hydration**: Control which data sources take precedence
- **Fallback Pattern**: Graceful degradation with source fallbacks

### v2.0 New Features

#### Application Context

Set context values from your application before hydration:

```php
$dataMapper = new DataMapper($serviceResolver);

// Add application-level context
$dataMapper->addToContext('feature_enabled', $featureFlag->isEnabled());
$dataMapper->addManyToContext([
    'env' => 'production',
    'user_role' => 'admin',
]);

// Use in expressions
#[MultiPropDataSource(
    args: "expr(context('feature_enabled') ? property('propA') : property('propB'))"
)]
```

#### Data Lineage Collection

Track data flow during hydration for debugging:

```php
$dataMapper->enableLineageCollection();

// ... hydration happens ...

$collector = $dataMapper->getLineageCollector();
$events = $collector->getEvents();
$summary = $collector->getSummary();
```

See [DataLineageCollector](docs/classes/DataLineageCollector.md) for details.

#### Priority System

All DataSource attributes now support a `priority` field (integer, default: 0). Higher priority sources can override values from lower priority sources:

```php
// Low priority - loaded from cache first
#[DataSource(
    class: CacheService::class,
    method: 'getCached',
    priority: 0
)]
private ?string $name = null;

// High priority - overrides cached value when loaded
#[DataSource(
    class: ApiService::class,
    method: 'getFromApi',
    priority: 10
)]
private ?string $name = null;
```

#### Fallback Pattern

DataSourceRef now uses `id` + `fallbacks` instead of `chain` for clearer semantics:

```php
#[DataSourceRef(
    id: 'primarySource',
    fallbacks: ['backupSource', 'lastResort'],
    exceptionOnNoValidDataSource: NoValidDataSourceException::class,
    priority: 5
)]
private ?string $data = null;
```

#### Build-Time Validation

Validate your metadata attributes before runtime:

```bash
# Validate a single class
./bin/datamapper datamapper:validate:class 'App\Entity\User'

# Validate all classes in a directory
./bin/datamapper datamapper:validate src/Entity

# Fail on warnings
./bin/datamapper datamapper:validate src/Entity --fail-on-warning
```

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
- `id`: Single DataSource (optionally with `fallbacks`)
- `fallbacks`: Array of fallback source IDs to try if primary fails
- `providers`: Aggregation mode (merge results from multiple sources)
- `priority`: Hydration priority (higher values override lower)

```php
// Simple reference
#[DataSourceRef(id: 'userData')]

// With fallbacks (tries primary, then fallbacks in order)
#[DataSourceRef(
    id: 'primaryApi',
    fallbacks: ['cacheBackup', 'defaultValues'],
    exceptionOnNoValidDataSource: NoValidDataSourceException::class
)]

// Aggregation (merges all provider results)
#[DataSourceRef(providers: ['basicInfo', 'extendedInfo', 'preferences'])]
```

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

#### PropertyInstantiatingHook

Execute logic after object instantiation, before hydration:

```php
use Kassko\DataMapper\Attribute\PropertyInstantiatingHook;

#[PropertyInstantiatingHook(after_instantiating: 'initialize', args: ['##object'])]
class Entity
{
    public function initialize(self $obj): void {
        // Initialization logic
    }
}
```

See [docs/attributes/PropertyInstantiatingHook.md](docs/attributes/PropertyInstantiatingHook.md) for details.

#### PropertyHydratingHook

Execute logic before and/or after object hydration:

```php
use Kassko\DataMapper\Attribute\PropertyHydratingHook;

#[PropertyHydratingHook(
    before_hydrate_object: 'prepare',
    after_hydrate_object: 'finalize'
)]
class Entity
{
    public function prepare(array $rawData): void {
        // Before hydration
    }
    
    public function finalize(?object $object, array $rawData): void {
        // After hydration
    }
}
```

See [docs/attributes/PropertyHydratingHook.md](docs/attributes/PropertyHydratingHook.md) for details.

#### PropertySettingHook

Property-level lifecycle hooks with support for external services:

```php
use Kassko\DataMapper\Attribute\PropertySettingHook;

class Entity
{
    // Hook on the data object itself
    #[PropertySettingHook(
        before_set_property: 'validate',
        after_set_property: 'onSet',
        args: ["expr(rawDataItem('name'))", '#name']
    )]
    private ?string $name = null;
    
    public function validate(string $rawName): void {
        // Validation logic
    }
    
    public function onSet(string $name): void {
        // Post-set logic
    }
}

// Hook on external service
#[PropertySettingHook(
    after_set_property: 'validateEmail',
    class: ValidationService::class,  // External service
    args: ['##object', '#email']
)]
private ?string $email = null;
```

When `class` is provided, the hook calls the method on the external service instead of the data object.

See [docs/attributes/PropertySettingHook.md](docs/attributes/PropertySettingHook.md) for details.

#### CustomHydrator

Define custom hydration logic for complex scenarios:

```php
use Kassko\DataMapper\Attribute\CustomHydrator;

class Document
{
    #[CustomHydrator(
        key: 'complex_parser',
        objectClass: ContentInterface::class
    )]
    private ?ContentInterface $content = null;
}

// Register the hydrator
$builder->addCustomHydrator('complex_parser', function(array $data): ?object {
    return match($data['type'] ?? null) {
        'text' => new TextContent($data),
        'html' => new HtmlContent($data),
        default => null,
    };
});
```

See [docs/attributes/CustomHydrator.md](docs/attributes/CustomHydrator.md) for details.

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

Add context variables for conditional hydration behavior:

```php
#[Context(shop_quality: 'premium', region: 'europe')]
private ?Shop $shop = null;
```

Context values accumulate through nested objects and can be accessed in expressions:

```php
#[PropertyCandidates([
    new PropertyCandidate(
        discriminator: "expr(context('shop_quality') === 'premium')",
        property: new Property(class: PremiumShop::class)
    ),
    new PropertyCandidate(
        discriminator: "expr(contextKeyExists('region'))",
        property: new Property(class: RegionalShop::class)
    )
])]
private ?Shop $shop = null;
```

See [Context](docs/attributes/Context.md) for detailed usage.

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
| `context('key')` | Get context value (null if missing, logs warning) |
| `contextKeyExists('key')` | Check if context key exists |
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