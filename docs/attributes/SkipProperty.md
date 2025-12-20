# SkipProperty

Excludes a property from hydration.

## Usage

```php
use Kassko\DataMapper\Attribute\SkipProperty;

class Entity
{
    private ?string $name = null;  // Will be hydrated
    
    #[SkipProperty]
    private ?string $internal = null;  // Will NOT be hydrated
}
```

## See Also

- [KeepProperty](KeepProperty.md)
- [SkipAllProperties](SkipAllProperties.md)
- [KeepAllProperties](KeepAllProperties.md)
