# PropertyCandidates

Enables runtime type resolution for polymorphic properties.

## Usage

```php
use Kassko\DataMapper\Attribute\PropertyCandidates;
use Kassko\DataMapper\Attribute\PropertyCandidate;
use Kassko\DataMapper\Attribute\Property;

class Garage
{
    #[PropertyCandidates([
        new PropertyCandidate(
            discriminator: "expr(rawDataItemExists('gasolineKind'))",
            property: new Property(class: GasolineCar::class)
        ),
        new PropertyCandidate(
            discriminator: "expr(rawDataItemExists('energyProvider'))",
            property: new Property(class: ElectricCar::class)
        )
    ])]
    private array $cars = [];
}
```

## Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `candidates` | `array` | Yes | Array of PropertyCandidate instances |

## How It Works

1. Evaluates each candidate's discriminator expression
2. Uses the first matching candidate's property configuration
3. Hydrates the value using the resolved type

## See Also

- [PropertyCandidate](PropertyCandidate.md)
- [Property](Property.md)
