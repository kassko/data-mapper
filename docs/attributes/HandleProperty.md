# HandleProperty

Property-level attribute that controls whether a specific property should be hydrated.

## Usage

```php
use Kassko\DataMapper\Attribute\HandleProperty;
use Kassko\DataMapper\Attribute\Property;

class User
{
    #[Property(key: 'name')]
    private string $name;

    // Always skip this property
    #[Property(key: 'internal_note')]
    #[HandleProperty(value: false)]
    private string $internalNote;

    // Conditionally include based on context
    #[Property(key: 'email')]
    #[HandleProperty(value: true, when: "expr(contextKeyExists('include_email'))")]
    private string $email;

    // Conditionally skip based on context
    #[Property(key: 'ssn')]
    #[HandleProperty(value: false, when: "expr(contextKeyExists('hide_sensitive'))")]
    private string $ssn;
}
```

## Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `value` | `bool` | No | `true` | If `true`, include the property. If `false`, skip the property. |
| `when` | `?string` | No | `null` | Expression to evaluate. When present, the behavior depends on both `value` and the expression result. |
| `cascade` | `bool` | No | `true` | Whether this attribute cascades to child classes |
| `enabled` | `bool` | No | `true` | Whether this attribute is active (disabled attributes are ignored) |

## Behavior with `when` Expression

The `when` parameter creates a conditional behavior that follows the "except" pattern:

| value | when result | Hydration |
|-------|-------------|-----------|
| `true` | `true` | ✅ Hydrate |
| `true` | `false` | ❌ Skip (EXCEPT) |
| `false` | `true` | ❌ Skip |
| `false` | `false` | ✅ Hydrate (EXCEPT) |

### Examples

```php
// Include email only when 'include_email' context is set
#[HandleProperty(value: true, when: "expr(contextKeyExists('include_email'))")]
// - include_email=true → hydrate
// - include_email=false/missing → skip

// Skip SSN only when 'hide_sensitive' context is set
#[HandleProperty(value: false, when: "expr(contextKeyExists('hide_sensitive'))")]
// - hide_sensitive=true → skip
// - hide_sensitive=false/missing → hydrate
```

## Without `when` Expression

When `when` is not specified, the `value` parameter directly controls hydration:

- `HandleProperty(value: true)` - Always hydrate the property
- `HandleProperty(value: false)` - Always skip the property

## Interaction with HandleAllProperties

`HandleProperty` takes precedence over `HandleAllProperties`:

| HandleAllProperties | HandleProperty | Result |
|---------------------|----------------|--------|
| `value: true` | not present | Hydrate |
| `value: true` | `value: false` | Skip |
| `value: false` | not present | Skip |
| `value: false` | `value: true` | Hydrate |

## Cascading

When `cascade: true` (default), child classes inherit the parent's `HandleProperty` settings unless they define their own.

## See Also

- [HandleAllProperties](HandleAllProperties.md) - Class-level default hydration control
- [Property](Property.md) - Property mapping configuration
- [Expression Language](../concepts/ExpressionLanguage.md) - Using expressions in `when`
