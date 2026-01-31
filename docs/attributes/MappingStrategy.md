# MappingStrategy

Defines a mapping strategy for converting source field names (various cases) to property names (camelCase). Can be applied at class level or property level.

## ⚠️ Important: Enabling MappingStrategy

MappingStrategy feature is **disabled by default** for performance reasons. You must explicitly enable it before using MappingStrategy attributes.

### Enabling via DataMapperBuilder

```php
use Kassko\DataMapper\DataMapperBuilder;

$dataMapper = (new DataMapperBuilder())
    ->enableMappingStrategy()  // Required to use MappingStrategy
    ->build();
```

### Enabling via Symfony Bundle

```yaml
# config/packages/kassko_data_mapper.yaml
kassko_data_mapper:
    mapping_strategy:
        enabled: true  # Enable MappingStrategy feature
    
    # Optional: Enable caching for better performance
    mapping_cache:
        enabled: true
        service: 'cache.app'  # PSR-16 cache service
```

> **Note:** Using MappingStrategy without enabling it will throw a `MappingStrategyException`.

## Overview

When working with external data sources (APIs, databases, files), field names often use different naming conventions than PHP camelCase properties. `MappingStrategy` provides automatic conversion between these conventions.

**Supported source case formats:**
- `from_common_cases_mix` (default): handles underscore, dash, camelCase, and mixed formats
- `from_camel_case`: source is already camelCase (e.g., `firstName`)
- `from_underscore_case`: source uses underscores (e.g., `first_name`)
- `from_dash_case`: source uses dashes (e.g., `first-name`)
- `from_pascal_case`: source uses PascalCase (e.g., `FirstName`)
- `from_snake_case`: source uses Snake_Case (e.g., `First_Name`)
- `from_constant_case`: source uses CONSTANT_CASE (e.g., `FIRST_NAME`)
- `from_upper_dash_case`: source uses UPPER-DASH-CASE (e.g., `FIRST-NAME`)

## Usage

### Class-Level Strategy

Apply to all properties in a class:

```php
use Kassko\DataMapper\Attribute\MappingStrategy;
use Kassko\DataMapper\Enum\MappingStrategyPreset;

#[MappingStrategy(preset: MappingStrategyPreset::FROM_UNDERSCORE_CASE)]
class Person
{
    private ?string $firstName = null;  // Maps from 'first_name'
    private ?string $lastName = null;   // Maps from 'last_name'
    private ?string $billingAddress = null; // Maps from 'billing_address'
}
```

### Property-Level Override

Override the class-level strategy for specific properties:

```php
use Kassko\DataMapper\Attribute\MappingStrategy;
use Kassko\DataMapper\Enum\MappingStrategyPreset;

#[MappingStrategy(preset: MappingStrategyPreset::FROM_UNDERSCORE_CASE)]
class Person
{
    private ?string $firstName = null;  // Uses class strategy: 'first_name'
    
    #[MappingStrategy(preset: MappingStrategyPreset::FROM_DASH_CASE)]
    private ?string $lastName = null;   // Override: 'last-name'
    
    private ?string $billingAddress = null; // Uses class strategy: 'billing_address'
}
```

### String Preset Values

You can use string values instead of the enum:

```php
#[MappingStrategy(preset: 'from_underscore_case')]
class Person
{
    private ?string $firstName = null;
}
```

### Custom Callable

For complex mapping logic, use a custom callable:

```php
use Kassko\DataMapper\Attribute\MappingStrategy;

#[MappingStrategy(custom: [CustomMappingService::class, 'mapPropertyToSource'])]
class Person
{
    private ?string $firstName = null;
    private ?string $lastName = null;
}
```

The custom callable signature:

```php
class CustomMappingService
{
    /**
     * @param string $propertyName The camelCase property name
     * @param array $data The source data
     * @param array $args Additional arguments from the attribute
     * @return string The source field name
     */
    public function mapPropertyToSource(string $propertyName, array $data, array $args): string
    {
        // Custom mapping logic
        return 'custom_' . strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $propertyName));
    }
}
```

### Default Behavior (No Attribute)

When no `MappingStrategy` attribute is defined, the default strategy `from_common_cases_mix` is used. This handles:
- `first_name` → `firstName`
- `last-name` → `lastName`
- `billingAddress` → `billingAddress` (already camelCase)
- `home-delivery_address` → `homeDeliveryAddress` (mixed format)

```php
// No MappingStrategy attribute - uses default from_common_cases_mix
class Person
{
    private ?string $firstName = null;  // Maps from 'first_name', 'first-name', or 'firstName'
    private ?string $lastName = null;
}
```

## Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `preset` | `MappingStrategyPreset\|string\|null` | `null` | Predefined mapping strategy |
| `custom` | `array\|null` | `null` | Custom callable `[class, method]` |
| `args` | `array` | `[]` | Additional arguments for custom callable |
| `cascade` | `bool` | `true` | Whether to cascade to child classes |
| `enabled` | `bool` | `true` | Whether this attribute is active |

## Preset Strategies

| Preset | Source Example | Target |
|--------|---------------|--------|
| `FROM_COMMON_CASES_MIX` | `first_name`, `first-name`, `firstName` | `firstName` |
| `FROM_CAMEL_CASE` | `firstName` | `firstName` |
| `FROM_UNDERSCORE_CASE` | `first_name` | `firstName` |
| `FROM_DASH_CASE` | `first-name` | `firstName` |
| `FROM_PASCAL_CASE` | `FirstName` | `firstName` |
| `FROM_SNAKE_CASE` | `First_Name` | `firstName` |
| `FROM_CONSTANT_CASE` | `FIRST_NAME` | `firstName` |
| `FROM_UPPER_DASH_CASE` | `FIRST-NAME` | `firstName` |

## Validation Rules

1. **Mutual Exclusivity**: `preset` and `custom` cannot both be set
2. **Required Configuration**: Either `preset` or `custom` must be defined (unless `enabled: false`)
3. **sourceField Conflict**: `Property::sourceField` and `MappingStrategy` on the same property are mutually exclusive

```php
// ❌ Invalid: both preset and custom
#[MappingStrategy(preset: 'from_underscore_case', custom: [MyMapper::class, 'map'])]

// ❌ Invalid: neither preset nor custom (when enabled)
#[MappingStrategy()]

// ❌ Invalid: both sourceField and MappingStrategy on same property
#[Property(sourceField: 'first_name')]
#[MappingStrategy(preset: 'from_dash_case')]
private ?string $firstName = null;

// ✅ Valid: disabled with no configuration
#[MappingStrategy(enabled: false)]
```

## Priority

1. **Highest**: `Property::sourceField` (explicit mapping)
2. **Medium**: Property-level `MappingStrategy`
3. **Lowest**: Class-level `MappingStrategy`
4. **Default**: `from_common_cases_mix` (when no mapping defined)

## Performance Considerations

Case conversion operations can be expensive, especially with large datasets or frequently accessed objects. To optimize performance:

### Using Mapping Cache

Enable the mapping cache to cache case conversion results:

```php
use Kassko\DataMapper\DataMapperBuilder;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;

$cache = new Psr16Cache(new FilesystemAdapter());

$dataMapper = (new DataMapperBuilder())
    ->enableMappingStrategy()
    ->setMappingCache($cache)  // PSR-16 cache for conversions
    ->build();
```

### Bundle Configuration

```yaml
kassko_data_mapper:
    mapping_strategy:
        enabled: true
    mapping_cache:
        enabled: true
        service: 'cache.app'  # Use Symfony's PSR-16 cache
```

### In-Memory Cache (Default)

When no external cache is provided, an in-memory array cache is used automatically. This provides good performance within a single request but doesn't persist across requests.

## See Also

- [Property](Property.md) - For explicit sourceField mapping
- [PropertyConfig](PropertyConfig.md) - For reusable property configurations
