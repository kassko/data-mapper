# Hook

Defines callbacks to execute at specific lifecycle events during hydration.

## Usage

```php
use Kassko\DataMapper\Attribute\Hook;

#[Hook(name: 'after_create_object', method: 'onCreated', args: ['##this'])]
class Entity
{
    private bool $initialized = false;
    
    public function onCreated(self $entity): void
    {
        $this->initialized = true;
    }
}
```

## Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `name` | `string` | Yes | Lifecycle event name |
| `method` | `string` | Yes | Method to call |
| `args` | `array` | No | Arguments to pass to method |

## Lifecycle Events

| Event | When | Args Available |
|-------|------|----------------|
| `after_create_object` | After object instantiation | `##this` |
| `after_set_property` | After property value is set | `##this`, `#propertyName` |

## Special Arguments

- `##this` - Reference to the current object
- `#propertyName` - Value of the specified property

## Examples

### Object Initialization

```php
#[Hook(name: 'after_create_object', method: 'initialize', args: ['##this'])]
class Entity
{
    public function initialize(self $entity): void
    {
        // Post-construction logic
    }
}
```

### Property Change Tracking

```php
class Entity
{
    #[Hook(name: 'after_set_property', method: 'onNameChanged', args: ['##this', '#name'])]
    private ?string $name = null;
    
    public function onNameChanged(self $entity, ?string $name): void
    {
        // Track name changes
    }
}
```

## See Also

- [Property](Property.md)
