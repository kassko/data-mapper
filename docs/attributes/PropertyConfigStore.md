# PropertyConfigStore

Class-level attribute that stores reusable `PropertyConfig` definitions.

## Purpose

`PropertyConfigStore` allows you to define multiple property configurations at class level. These configurations can then be referenced by ID from `Property` attributes using:
- `config`: Reference a single configuration
- `configCandidates` + `defaultConfigCandidate`: Polymorphic resolution based on rules

## Usage

### Basic Setup

```php
use Kassko\DataMapper\Attribute\PropertyConfig;
use Kassko\DataMapper\Attribute\PropertyConfigStore;
use Kassko\DataMapper\Attribute\Property;

#[PropertyConfigStore([
    new PropertyConfig(id: 'gasolineCar', class: GasolineCar::class),
    new PropertyConfig(id: 'electricCar', class: ElectricCar::class),
    new PropertyConfig(id: 'hybridCar', class: HybridCar::class),
])]
class Garage
{
    // Use single config reference
    #[Property(config: 'gasolineCar')]
    private ?Car $mainCar = null;

    // Use polymorphic resolution with configCandidates
    #[Property(
        configCandidates: [
            ['id' => 'gasolineCar', 'when' => "expr(rawDataItemExists('gasolineKind'))"],
            ['id' => 'electricCar', 'when' => "expr(rawDataItemExists('energyProvider'))"],
        ],
        defaultConfigCandidate: 'hybridCar'
    )]
    private array $cars = [];
}
```

### Polymorphic Hydration

The `configCandidates` mechanism allows runtime selection of property configuration based on the data being hydrated:

1. Each candidate has a `when` expression that is evaluated against the raw data item
2. The first candidate whose expression evaluates to `true` is selected
3. If no expression matches, `defaultConfigCandidate` is used as fallback

```php
#[PropertyConfigStore([
    new PropertyConfig(id: 'admin', class: AdminUser::class),
    new PropertyConfig(id: 'regular', class: RegularUser::class),
])]
class UserContainer
{
    #[Property(
        configCandidates: [
            ['id' => 'admin', 'when' => "expr(rawDataItem('role') == 'admin')"],
        ],
        defaultConfigCandidate: 'regular'
    )]
    private array $users = [];
}
```

## Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `configs` | `PropertyConfig[]` | Yes | Array of PropertyConfig instances |

## Validation

- All items in the array must be `PropertyConfig` instances
- Config IDs must be unique within the store
- Duplicate IDs will throw an exception

## Expression Functions

The `when` expression can use:
- `rawDataItemExists('key')` - Check if key exists in data item
- `rawDataItem('key')` - Get value of key from data item
- `context('key')` - Get value from context registry
- `contextKeyExists('key')` - Check if key exists in context

## Attribute Cascading

`PropertyConfigStore` supports cascading from parent classes and traits. Child classes can reference PropertyConfigs defined in:

- Parent class `PropertyConfigStore`
- Trait `PropertyConfigStore`
- Their own `PropertyConfigStore`

When the same ID is defined in multiple places, the child's definition wins.

```php
// Parent defines configs
#[PropertyConfigStore([
    new PropertyConfig(id: 'parentConfig', class: ParentProduct::class),
])]
abstract class BaseContainer {}

// Trait defines additional configs
#[PropertyConfigStore([
    new PropertyConfig(id: 'traitConfig', class: TraitProduct::class),
])]
trait ConfigTrait {}

// Child can reference configs from parent and trait
class ChildContainer extends BaseContainer
{
    use ConfigTrait;
    
    #[Property(
        configCandidates: [
            ['id' => 'parentConfig', 'when' => "expr(rawDataItemExists('parentType'))"], // Works!
            ['id' => 'traitConfig', 'when' => "expr(rawDataItemExists('traitType'))"], // Works!
        ],
        defaultConfigCandidate: 'parentConfig'
    )]
    private ?object $item = null;
}
```

See [Attribute Cascading](AttributeCascading.md) for full details.

## See Also

- [PropertyConfig](PropertyConfig.md) - Individual configuration definitions
- [Property](Property.md) - Property attribute that references configs
- [Attribute Cascading](AttributeCascading.md)
