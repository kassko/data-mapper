# SkipProperty

Excludes a property from hydration, optionally with a conditional expression.

## Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `when` | `string\|null` | `null` | Optional expression to conditionally skip the property. If null, property is always skipped. If expression evaluates to true, property is skipped. If false, property is not skipped. |
| `cascade` | `bool` | `true` | Whether this attribute cascades to child classes |

## Usage

### Basic Usage

```php
use Kassko\DataMapper\Attribute\SkipProperty;

class Entity
{
    private ?string $name = null;  // Will be hydrated
    
    #[SkipProperty]
    private ?string $internal = null;  // Will NOT be hydrated
}
```

### Conditional Skip with Expression

```php
use Kassko\DataMapper\Attribute\SkipProperty;

class Entity
{
    private ?string $firstName = null;  // Always hydrated
    private ?string $lastName = null;   // Always hydrated
    
    #[SkipProperty(when: "expr(contextKeyExists('hide_email'))")]
    private ?string $email = null;  // Skipped only if 'hide_email' context key exists
}
```

### Expression Examples

```php
// Skip based on context key existence
#[SkipProperty(when: "expr(contextKeyExists('minimal_mode'))")]
private ?string $details = null;

// Skip based on context value
#[SkipProperty(when: "expr(context('user_role') !== 'admin')")]
private ?string $adminNotes = null;

// Skip based on environment variable
#[SkipProperty(when: "expr(envVarExists('PRODUCTION'))")]
private ?string $debugInfo = null;

// Skip based on raw data
#[SkipProperty(when: "expr(rawDataItemExists('skip_extended'))")]
private ?string $extendedInfo = null;
```

## Behavior

- If `when` is null (default), the property is **always skipped**
- If `when` expression evaluates to `true`, the property is skipped
- If `when` expression evaluates to `false`, the property is **not skipped** and continues with normal hydration
- Non-boolean expression results are coerced to boolean with a warning logged

## See Also

- [KeepProperty](KeepProperty.md)
- [SkipAllProperties](SkipAllProperties.md)
- [KeepAllProperties](KeepAllProperties.md)
