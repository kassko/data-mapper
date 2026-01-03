# Property

Maps a property name to a different key in the raw data and configures hydration behavior. Supports direct configuration or references to reusable configurations via `PropertyConfigStore`.

## Usage

### Direct Configuration

```php
use Kassko\DataMapper\Attribute\Property;

class Person
{
    #[Property(key: 'first_name')]
    private ?string $firstName = null;
    
    #[Property(
        key: 'address_data',
        class: Address::class,
        mapping: ['billing_street' => 'street', 'billing_city' => 'city']
    )]
    private ?Address $billingAddress = null;
}
```

### Using PropertyConfigStore Reference

```php
use Kassko\DataMapper\Attribute\Property;
use Kassko\DataMapper\Attribute\PropertyConfig;
use Kassko\DataMapper\Attribute\PropertyConfigStore;

#[PropertyConfigStore([
    new PropertyConfig(id: 'address', class: Address::class),
])]
class Person
{
    #[Property(config: 'address')]
    private ?Address $mainAddress = null;
}
```

### Polymorphic Hydration with configCandidates

```php
use Kassko\DataMapper\Attribute\Property;
use Kassko\DataMapper\Attribute\PropertyConfig;
use Kassko\DataMapper\Attribute\PropertyConfigStore;

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
| `key` | `?string` | No | Key in raw data array |
| `class` | `?string` | No | Class for nested object hydration |
| `mapping` | `?array` | No | Instance-specific key mapping (requires `class`) |
| `expand` | `?string` | No | Comma-separated fields to expand |
| `noExpand` | `?string` | No | Comma-separated fields to skip |
| `config` | `?string` | No | Reference to a PropertyConfig by ID |
| `configCandidates` | `?array` | No | Array of config candidates with `when` expressions |
| `defaultConfigCandidate` | `?string` | No | Default config ID if no rule matches |
| `keepWhen` | `?string` | No | Expression to conditionally include this Property |
| `cascade` | `bool` | No (default: true) | Whether this attribute cascades to child classes |

## Conditional Property Inclusion (keepWhen)

Use `keepWhen` to conditionally enable the Property attribute based on runtime context:

```php
use Kassko\DataMapper\Attribute\Property;
use Kassko\DataMapper\Attribute\SkipAllProperties;

#[SkipAllProperties]
class Entity
{
    #[Property(key: 'user_name', keepWhen: "expr(contextKeyExists('include_name'))")]
    private ?string $name = null;  // Only hydrated if 'include_name' context key exists
    
    private ?string $temp = null;  // Never hydrated (no Property attribute)
}
```

**Behavior:**
- If `keepWhen` is null (default), Property is always considered present
- If `keepWhen` expression evaluates to `true`, Property is active
- If `keepWhen` expression evaluates to `false`, Property is treated as absent
- Non-boolean results are coerced with a warning logged

## Validation Rules

- `mapping` can only be set when `class` is also specified
- `config` is mutually exclusive with `class`, `expand`, `noExpand`, and `mapping`
- `configCandidates` is mutually exclusive with `class`, `expand`, `noExpand`, `mapping`, and `config`
- `configCandidates` and `defaultConfigCandidate` must both be present or both absent
- Each configCandidate must have `id` and `when` keys

## Modes

### Direct Configuration

Set `class`, `mapping`, etc. directly on the property:

```php
#[Property(class: Address::class)]
private ?Address $address = null;
```

### Config Reference

Reference a PropertyConfig by ID:

```php
#[Property(config: 'address')]
private ?Address $address = null;
```

### Polymorphic Resolution (configCandidates)

Select a configuration based on runtime data evaluation:

```php
#[Property(
    configCandidates: [
        ['id' => 'typeA', 'when' => "expr(rawDataItem('type') == 'A')"],
        ['id' => 'typeB', 'when' => "expr(rawDataItem('type') == 'B')"],
    ],
    defaultConfigCandidate: 'typeDefault'
)]
private ?Item $item = null;
```

**configCandidate Structure:**
- `id` (required): Reference to a PropertyConfig ID
- `when` (required): Expression that returns a boolean

**Resolution Logic:**
1. Each candidate's `when` expression is evaluated against the raw data item
2. First candidate whose expression evaluates to `true` is selected
3. If no expression matches, `defaultConfigCandidate` is used

## Expression Functions

- `rawDataItemExists('key')` - Check if key exists in data item
- `rawDataItem('key')` - Get value of key from data item
- `context('key')` - Get value from context registry
- `contextKeyExists('key')` - Check if key exists in context

## See Also

- [PropertyConfig](PropertyConfig.md) - Individual configuration definitions
- [PropertyConfigStore](PropertyConfigStore.md) - Class-level attribute for storing configs
- [KeepProperty](KeepProperty.md)
- [SkipProperty](SkipProperty.md)
