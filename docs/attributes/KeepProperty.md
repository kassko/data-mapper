# KeepProperty

Marks a property for hydration even when the class has `SkipAllProperties`.

## Usage

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

## See Also

- [SkipAllProperties](SkipAllProperties.md)
- [SkipProperty](SkipProperty.md)
- [KeepAllProperties](KeepAllProperties.md)
