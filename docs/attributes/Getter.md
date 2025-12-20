# Getter

Specifies a custom getter method for a property.

## Usage

```php
use Kassko\DataMapper\Attribute\Getter;

class Entity
{
    #[Getter(method: 'getFullName')]
    private ?string $name = null;
    
    public function getFullName(): ?string
    {
        return "Mr. " . $this->name;
    }
}
```

## Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `method` | `string` | Yes | Name of the getter method |

## See Also

- [Setter](Setter.md)
