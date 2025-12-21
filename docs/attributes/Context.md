# Context

Stores hydrated data for later access.

## Usage

```php
use Kassko\DataMapper\Attribute\Context;

class Entity
{
    #[Context]
    private ?array $rawData = null;
    
    // After hydration, $rawData will contain the original data
}
```

## Parameters

This attribute takes no parameters.

## Behavior

- Stores the raw data used to hydrate the property
- Useful for debugging or accessing original values
- Data is stored after hydration completes

## See Also

- [Property](Property.md)
