# Property

Maps a property name to a different key in the raw data and configures hydration behavior.

## Usage

```php
use Kassko\DataMapper\Attribute\Property;

class Person
{
    #[Property(name: 'first_name')]
    private ?string $firstName = null;
    
    #[Property(
        name: 'address_data',
        class: Address::class,
        mapping: ['billing_street' => 'street', 'billing_city' => 'city']
    )]
    private ?Address $billingAddress = null;
}
```

## Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `name` | `?string` | No | Key in raw data array |
| `class` | `?string` | No | Class for nested object hydration |
| `mapping` | `?array` | No | Instance-specific key mapping |
| `expand` | `?string` | No | Comma-separated fields to expand |
| `noExpand` | `?string` | No | Comma-separated fields to skip |

## Examples

### Simple Name Mapping

```php
#[Property(name: 'user_email')]
private ?string $email = null;
```

### Nested Object Hydration

```php
#[Property(class: Address::class)]
private ?Address $address = null;
```

### Instance Mapping

Extract specific keys from flat data into nested object:

```php
#[Property(
    class: Address::class,
    mapping: ['delivery_street' => 'street', 'delivery_city' => 'city']
)]
private ?Address $deliveryAddress = null;
```

## See Also

- [KeepProperty](KeepProperty.md)
- [SkipProperty](SkipProperty.md)
