# PropertyCandidates

Enables runtime configuration selection based on dynamic conditions. While commonly used for polymorphism, it supports any runtime decision-making based on data, environment, context, or other runtime elements.

## Use Cases

| Use Case | Description | Example |
|----------|-------------|---------|
| **Polymorphism** | Different class based on data type | Car type based on fuel/battery |
| **Data-driven** | Configuration based on other property values | Shipping based on `#isPremium` |
| **Environment** | Different behavior for different environments | Logger based on `envVar('APP_ENV')` |
| **Context** | Based on runtime context | Features based on `context('tenant_type')` |
| **Runtime state** | Any runtime condition | Any dynamic selection criteria |

## Usage Examples

### Polymorphism (Common Use Case)

```php
use Kassko\DataMapper\Attribute\PropertyCandidates;
use Kassko\DataMapper\Attribute\PropertyCandidate;
use Kassko\DataMapper\Attribute\Property;

class Garage
{
    #[PropertyCandidates([
        new PropertyCandidate(
            discriminator: "expr(rawDataItemExists('fuel_type'))",
            property: new Property(class: GasolineCar::class)
        ),
        new PropertyCandidate(
            discriminator: "expr(rawDataItemExists('battery'))",
            property: new Property(class: ElectricCar::class)
        )
    ])]
    private array $cars = [];
}
```

### Based on Other Property Value

```php
class Order
{
    private bool $isPremium = false;
    
    #[PropertyCandidates([
        new PropertyCandidate(
            discriminator: "expr(#isPremium == true)",
            property: new Property(class: PremiumShipping::class)
        ),
        new PropertyCandidate(
            discriminator: "expr(#isPremium == false)",
            property: new Property(class: StandardShipping::class)
        )
    ])]
    private ?Shipping $shipping = null;
}
```

### Based on Environment

```php
class Application
{
    #[PropertyCandidates([
        new PropertyCandidate(
            discriminator: "expr(envVar('APP_ENV') == 'production')",
            property: new Property(class: ProductionLogger::class)
        ),
        new PropertyCandidate(
            discriminator: "expr(envVar('APP_ENV') == 'development')",
            property: new Property(class: DebugLogger::class)
        )
    ])]
    private ?Logger $logger = null;
}
```

### Based on Context

```php
class Tenant
{
    #[PropertyCandidates([
        new PropertyCandidate(
            discriminator: "expr(context('tenant_type') == 'enterprise')",
            property: new Property(class: EnterpriseFeatures::class)
        ),
        new PropertyCandidate(
            discriminator: "expr(context('tenant_type') == 'basic')",
            property: new Property(class: BasicFeatures::class)
        )
    ])]
    private ?Features $features = null;
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
