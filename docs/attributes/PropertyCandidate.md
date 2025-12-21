# PropertyCandidate

Defines a candidate type for polymorphic property resolution.

## Usage

Used within `PropertyCandidates`:

```php
use Kassko\DataMapper\Attribute\PropertyCandidate;
use Kassko\DataMapper\Attribute\Property;

new PropertyCandidate(
    discriminator: "expr(rawDataItemExists('electricMotor'))",
    property: new Property(class: ElectricCar::class)
)
```

## Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `discriminator` | `string` | Yes | Expression to evaluate for this candidate |
| `property` | `Property` | Yes | Property configuration if discriminator matches |

## Discriminator Expressions

Common patterns:

```php
// Check if a key exists
"expr(rawDataItemExists('gasolineKind'))"

// Check a value
"expr(rawData['type'] == 'electric')"

// Complex logic
"expr(rawData['voltage'] > 100)"
```

## See Also

- [PropertyCandidates](PropertyCandidates.md)
- [Property](Property.md)
