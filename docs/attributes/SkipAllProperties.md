# SkipAllProperties

Excludes all properties from hydration by default.

## Usage

```php
use Kassko\DataMapper\Attribute\SkipAllProperties;
use Kassko\DataMapper\Attribute\KeepProperty;
use Kassko\DataMapper\Attribute\Property;

#[SkipAllProperties]
class Entity
{
    #[KeepProperty]
    private ?string $id = null;  // Hydrated (explicitly kept)
    
    #[Property(key: 'user_name')]
    private ?string $name = null;  // Hydrated (has Property attribute)
    
    private ?string $temp = null;  // NOT hydrated
}
```

## Behavior

When `SkipAllProperties` is used:
- Only properties with `KeepProperty` are hydrated
- Properties with `Property` attribute are also hydrated
- All other properties are skipped

## See Also

- [KeepAllProperties](KeepAllProperties.md)
- [KeepProperty](KeepProperty.md)
- [SkipProperty](SkipProperty.md)
