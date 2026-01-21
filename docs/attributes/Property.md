# Property

Maps a property name to a different key in the raw data and configures hydration behavior. Supports direct configuration or references to reusable configurations via `PropertyConfigStore`.

## Usage

### Direct Configuration

```php
use Kassko\DataMapper\Attribute\Property;

class Person
{
    #[Property(sourceField: 'first_name')]
    private ?string $firstName = null;
    
    #[Property(
        sourceField: 'address_data',
        class: Address::class,
        mapping: ['billing_street' => 'street', 'billing_city' => 'city']
    )]
    private ?Address $billingAddress = null;
}
```

### Collection Hydration with itemClass

Use `itemClass` to specify the class for collection items:

```php
use Kassko\DataMapper\Attribute\Property;

class Person
{
    #[Property(itemClass: Address::class)]
    private ?array $addresses = null;
}
```

This will hydrate each item in the `addresses` array as an `Address` object.

### Single Object Hydration with class

Use `class` for single object properties:

```php
use Kassko\DataMapper\Attribute\Property;

class Person
{
    #[Property(class: Address::class)]
    private ?Address $address = null;
}
```

### PHP Typehint Fallback

When `class` is not specified, the hydrator will use the PHP typehint as a fallback:

```php
class Person
{
    #[Property(sourceField: 'main_address')]
    private ?Address $mainAddress = null;  // Will use Address class from typehint
}
```

### Automatic Hydration from Native PHP Typehint (No Property Attribute)

Properties with instantiable class typehints are automatically hydrated **without** requiring an explicit `#[Property]` attribute:

```php
class Person
{
    private ?string $firstName = null;
    
    // No #[Property] attribute needed - uses native typehint
    private ?Address $address = null;
    
    // Custom collection class - also auto-hydrated
    private ?AddressCollection $addresses = null;
}
```

**Behavior:**
- If the property has an instantiable class typehint (not an interface, not abstract), the hydrator creates a synthetic Property attribute
- The class from the typehint is used for hydration
- Works with nullable types (`?Address`) and union types (`Address|null`)

**Requirements:**
- The typehint must be an instantiable class (not an interface, not abstract)
- The raw data must be an array

**When NOT to use:**
- If you need `sourceField` mapping (raw data key differs from property name)
- If you need `mapping` for nested key transformations
- If you need `itemClass` for collection item typing
- If you need `expand`/`noExpand` for selective hydration

### Using class and itemClass Together

Use both when you need a specific container class with typed items:

```php
use Kassko\DataMapper\Attribute\Property;

class Person
{
    #[Property(class: ArrayCollection::class, itemClass: Address::class)]
    private Collection $addresses;
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
| `sourceField` | `?string` | No | Key in raw data array |
| `class` | `?string` | No | Class for single object hydration (container type) |
| `itemClass` | `?string` | No | Class for collection item hydration (element type) |
| `mapping` | `?array` | No | Instance-specific key mapping (requires `class` or `itemClass`) |
| `expand` | `?string` | No | Comma-separated fields to expand |
| `noExpand` | `?string` | No | Comma-separated fields to skip |
| `config` | `?string` | No | Reference to a PropertyConfig by ID |
| `configCandidates` | `?array` | No | Array of config candidates with `when` expressions |
| `defaultConfigCandidate` | `?string` | No | Default config ID if no rule matches |
| `handleWhen` | `?string` | No | Expression to conditionally include this Property |
| `cascade` | `bool` | No (default: true) | Whether this attribute cascades to child classes |
| `enabled` | `bool` | No (default: true) | Whether this attribute is active |

## class vs itemClass

| Attribute | Purpose | Use Case |
|-----------|---------|----------|
| `class` | Class for single object hydration | Properties with a single nested object |
| `itemClass` | Class for collection item hydration | Properties with arrays/collections of objects |

**Examples:**

```php
// Single object - use class
#[Property(class: Address::class)]
private ?Address $address = null;

// Collection - use itemClass
#[Property(itemClass: Address::class)]
private ?array $addresses = null;

// Collection with specific container - use both
#[Property(class: ArrayCollection::class, itemClass: Address::class)]
private Collection $addresses;
```

## Conditional Property Inclusion (handleWhen)

Use `handleWhen` to conditionally enable the Property attribute based on runtime context:

```php
use Kassko\DataMapper\Attribute\Property;
use Kassko\DataMapper\Attribute\HandleAllProperties;

#[HandleAllProperties(value: false)]
class Entity
{
    #[Property(sourceField: 'user_name', handleWhen: "expr(contextKeyExists('include_name'))")]
    private ?string $name = null;  // Only hydrated if 'include_name' context key exists
    
    private ?string $temp = null;  // Never hydrated (no Property attribute)
}
```

**Behavior:**
- If `handleWhen` is null (default), Property is always considered present
- If `handleWhen` expression evaluates to `true`, Property is active
- If `handleWhen` expression evaluates to `false`, Property is treated as absent
- Non-boolean results are coerced with a warning logged

## Validation Rules

- `mapping` can only be set when `class` or `itemClass` is also specified
- `itemClass` cannot be used with scalar built-in types (string, int, float, bool). Use array, object, or class types.
- `class` must be compatible with the PHP typehint (same class or subclass)
- `class` cannot be used with scalar built-in types
- `config` is mutually exclusive with `class`, `itemClass`, `expand`, `noExpand`, and `mapping`
- `configCandidates` is mutually exclusive with `class`, `itemClass`, `expand`, `noExpand`, `mapping`, and `config`
- `configCandidates` and `defaultConfigCandidate` must both be present or both absent
- Each configCandidate must have `id` and `when` keys

## Type Resolution

1. For single objects:
   - If `class` is set, use it
   - Otherwise, fall back to PHP typehint class

2. For collections:
   - If `itemClass` is set, use it for each item
   - Otherwise, fall back to `class` for backward compatibility

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
- [HandleProperty](HandleProperty.md)
- [HandleAllProperties](HandleAllProperties.md)
