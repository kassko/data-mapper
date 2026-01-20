# PropertyConfig

Defines a reusable property configuration that can be referenced by ID from `Property` attributes.

## Purpose

`PropertyConfig` allows you to define property configurations at class level and reuse them for polymorphic hydration. This enables:
- **Reusability**: Define a config once, reference it multiple times
- **Polymorphic hydration**: Select different configurations based on runtime conditions
- **Cleaner code**: Keep property-level attributes minimal by referencing stored configs

## Usage

`PropertyConfig` must be used inside `PropertyConfigStore`:

```php
use Kassko\DataMapper\Attribute\PropertyConfig;
use Kassko\DataMapper\Attribute\PropertyConfigStore;
use Kassko\DataMapper\Attribute\Property;

#[PropertyConfigStore([
    new PropertyConfig(id: 'gasolineCar', class: GasolineCar::class),
    new PropertyConfig(id: 'electricCar', class: ElectricCar::class),
])]
class Garage
{
    #[Property(
        configCandidates: [
            ['id' => 'gasolineCar', 'when' => "expr(rawDataItemExists('gasolineKind'))"],
            ['id' => 'electricCar', 'when' => "expr(rawDataItemExists('energyProvider'))"],
        ],
        defaultConfigCandidate: 'gasolineCar'
    )]
    private array $cars = [];
}
```

## Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | `string` | Yes | Unique identifier for this configuration |
| `class` | `string` | No | Class for single object hydration (container type) |
| `itemClass` | `string` | No | Class for collection item hydration (element type) |
| `sourceField` | `string` | No | Field name in raw data (array key or DTO property) |
| `expand` | `string` | No | Comma-separated properties to expand |
| `noExpand` | `string` | No | Comma-separated properties to NOT expand |
| `mapping` | `array` | No | Instance-specific key mapping (requires `class` or `itemClass`) |

## class vs itemClass

| Attribute | Purpose | Use Case |
|-----------|---------|----------|
| `class` | Class for single object hydration | Configs for single nested objects |
| `itemClass` | Class for collection item hydration | Configs for arrays/collections of objects |

### Using itemClass for Collections

```php
use Kassko\DataMapper\Attribute\PropertyConfig;
use Kassko\DataMapper\Attribute\PropertyConfigStore;
use Kassko\DataMapper\Attribute\Property;

#[PropertyConfigStore([
    new PropertyConfig(id: 'addressCollection', itemClass: Address::class),
])]
class Person
{
    #[Property(config: 'addressCollection')]
    private array $addresses = [];
}
```

## Validation

- `id` is required and must be unique within the `PropertyConfigStore`
- `mapping` can only be set when `class` or `itemClass` is also specified

## See Also

- [PropertyConfigStore](PropertyConfigStore.md) - Class-level attribute that holds PropertyConfig instances
- [Property](Property.md) - Property attribute that references configs via `config`, `configCandidates`
- [Attribute Cascading](AttributeCascading.md) - Inherit PropertyConfigs from parent classes and traits
