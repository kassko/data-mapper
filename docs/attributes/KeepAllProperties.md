# KeepAllProperties

Marks all properties for hydration (default behavior).

## Usage

```php
use Kassko\DataMapper\Attribute\KeepAllProperties;

#[KeepAllProperties]  // This is the default, explicit declaration optional
class Entity
{
    private ?string $name = null;   // Hydrated
    private ?string $email = null;  // Hydrated
}
```

## Notes

- This is the default behavior when no skip/keep attributes are used
- Use explicitly to document intent
- Can be combined with `SkipProperty` on specific properties

## See Also

- [SkipAllProperties](SkipAllProperties.md)
- [KeepProperty](KeepProperty.md)
- [SkipProperty](SkipProperty.md)
