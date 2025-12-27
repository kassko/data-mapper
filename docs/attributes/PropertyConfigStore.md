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
            ['id' => 'gasolineCar', 'rule' => "expr(rawDataItemExists('gasolineKind'))"],
            ['id' => 'electricCar', 'rule' => "expr(rawDataItemExists('energyProvider'))"],
        ],
        defaultConfigCandidate: 'hybridCar'
    )]
    private array $cars = [];
}
```

### Polymorphic Hydration

The `configCandidates` mechanism allows runtime selection of property configuration based on the data being hydrated:

1. Each candidate has a `rule` expression that is evaluated against the raw data item
2. The first candidate whose rule evaluates to `true` is selected
3. If no rule matches, `defaultConfigCandidate` is used as fallback

```php
#[PropertyConfigStore([
    new PropertyConfig(id: 'admin', class: AdminUser::class),
    new PropertyConfig(id: 'regular', class: RegularUser::class),
])]
class UserContainer
{
    #[Property(
        configCandidates: [
            ['id' => 'admin', 'rule' => "expr(rawDataItem('role') == 'admin')"],
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

## Expression Functions for Rules

The `rule` expression can use:
- `rawDataItemExists('key')` - Check if key exists in data item
- `rawDataItem('key')` - Get value of key from data item
- `context('key')` - Get value from context registry
- `contextKeyExists('key')` - Check if key exists in context

## See Also

- [PropertyConfig](PropertyConfig.md) - Individual configuration definitions
- [Property](Property.md) - Property attribute that references configs
