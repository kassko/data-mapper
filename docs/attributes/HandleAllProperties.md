# HandleAllProperties

Class-level attribute that controls the default hydration behavior for all properties.

## Usage

```php
use Kassko\DataMapper\Attribute\HandleAllProperties;
use Kassko\DataMapper\Attribute\HandleProperty;
use Kassko\DataMapper\Attribute\Property;

// Skip all properties by default, only hydrate explicitly marked ones
#[HandleAllProperties(value: false)]
class User
{
    #[Property(sourceField: 'id')]
    #[HandleProperty(value: true)]  // Explicitly included
    private int $id;

    #[Property(sourceField: 'name')]
    #[HandleProperty(value: true)]  // Explicitly included
    private string $name;

    #[Property(sourceField: 'internal_note')]
    private string $internalNote;  // Will be skipped
}

// Handle all properties by default (this is the normal behavior)
#[HandleAllProperties(value: true)]
class Product
{
    #[Property(sourceField: 'name')]
    private string $name;  // Will be hydrated

    #[Property(sourceField: 'secret')]
    #[HandleProperty(value: false)]  // Explicitly excluded
    private string $secret;
}
```

## Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `value` | `bool` | No | `true` | If `true`, all properties are hydrated by default. If `false`, only properties with `HandleProperty(value: true)` are hydrated. |
| `cascade` | `bool` | No | `true` | Whether this attribute cascades to child classes |
| `enabled` | `bool` | No | `true` | Whether this attribute is active (disabled attributes are ignored) |

## Behavior

- `HandleAllProperties(value: true)` (default): All properties are hydrated unless explicitly excluded with `HandleProperty(value: false)`
- `HandleAllProperties(value: false)`: No properties are hydrated unless explicitly included with `HandleProperty(value: true)`

## Cascading

When `cascade: true` (default), child classes inherit the parent's `HandleAllProperties` setting unless they define their own.

## See Also

- [HandleProperty](HandleProperty.md) - Property-level inclusion/exclusion control
- [Property](Property.md) - Property mapping configuration
