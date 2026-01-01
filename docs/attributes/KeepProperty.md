# KeepProperty

Marks a property for hydration even when the class has `SkipAllProperties`.

## Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `when` | `string\|null` | `null` | Optional expression to conditionally keep the property. If null, property is always kept. If expression evaluates to true, property is kept. If false, property is not kept. |
| `cascade` | `bool` | `true` | Whether this attribute cascades to child classes |

## Usage

### Basic Usage

```php
use Kassko\DataMapper\Attribute\SkipAllProperties;
use Kassko\DataMapper\Attribute\KeepProperty;

#[SkipAllProperties]
class Entity
{
    #[KeepProperty]
    private ?string $id = null;  // Will be hydrated
    
    private ?string $temp = null;  // Will NOT be hydrated
}
```

### Conditional Keep with Expression

```php
use Kassko\DataMapper\Attribute\SkipAllProperties;
use Kassko\DataMapper\Attribute\KeepProperty;

#[SkipAllProperties]
class Entity
{
    #[KeepProperty]
    private ?string $id = null;  // Always hydrated
    
    #[KeepProperty(when: "expr(contextKeyExists('include_email'))")]
    private ?string $email = null;  // Hydrated only if 'include_email' context key exists
    
    private ?string $temp = null;  // Never hydrated
}
```

### Expression Examples

```php
// Keep based on context key existence
#[KeepProperty(when: "expr(contextKeyExists('detailed_mode'))")]
private ?string $details = null;

// Keep based on context value
#[KeepProperty(when: "expr(context('user_role') === 'admin')")]
private ?string $adminNotes = null;

// Keep based on environment variable
#[KeepProperty(when: "expr(envVarExists('DEBUG_MODE'))")]
private ?string $debugInfo = null;

// Keep based on raw data
#[KeepProperty(when: "expr(rawDataItemExists('extended_info'))")]
private ?string $extendedInfo = null;
```

## Behavior

- If `when` is null (default), the property is **always kept** for hydration
- If `when` expression evaluates to `true`, the property is kept
- If `when` expression evaluates to `false`, the `KeepProperty` attribute is treated as absent
- Non-boolean expression results are coerced to boolean with a warning logged

## See Also

- [SkipAllProperties](SkipAllProperties.md)
- [SkipProperty](SkipProperty.md)
- [KeepAllProperties](KeepAllProperties.md)
- [Property](Property.md) - for `keepWhen` field
