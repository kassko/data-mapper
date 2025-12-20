# Setter

Specifies a custom setter method for a property.

## Usage

```php
use Kassko\DataMapper\Attribute\Setter;

class Entity
{
    #[Setter(method: 'setName')]
    private ?string $name = null;
    
    public function setName(?string $value): void
    {
        $this->name = strtoupper($value);
    }
}
```

## Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `method` | `string` | Yes | Name of the setter method |

## See Also

- [Getter](Getter.md)
