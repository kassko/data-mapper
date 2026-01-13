# Getter

Specifies a custom getter method for a property. Supports different getter types including standard getters, issers (boolean checks), and hasers (existence checks).

## Basic Usage

```php
use Kassko\DataMapper\Attribute\Getter;

class Entity
{
    #[Getter(name: 'getFullName')]
    private ?string $name = null;
    
    public function getFullName(): ?string
    {
        return "Mr. " . $this->name;
    }
}
```

## Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `name` | `string` | Yes | - | Name of the getter method |
| `type` | `string` | No | `getter` | Type of getter: `getter`, `isser`, or `haser` |
| `cascade` | `bool` | No | `true` | Whether this attribute cascades to child classes |
| `enabled` | `bool` | No | `true` | Whether this attribute is active |

## Getter Types

### Standard Getter (`TYPE_GETTER`)

The default behavior. Returns the property value.

```php
use Kassko\DataMapper\Attribute\Getter;

class Person
{
    #[Getter(name: 'getFullName', type: Getter::TYPE_GETTER)]
    private ?string $name = null;
    
    public function getFullName(): ?string
    {
        return "Mr. " . $this->name;
    }
}
```

### Isser (`TYPE_ISSER`)

For boolean properties. Commonly used with `is*()` method naming convention.

```php
use Kassko\DataMapper\Attribute\Getter;

class User
{
    #[Getter(name: 'isActive', type: Getter::TYPE_ISSER)]
    private bool $active = false;
    
    public function isActive(): bool
    {
        return $this->active === true;
    }
}
```

### Haser (`TYPE_HASER`)

For checking if a value exists. Commonly used with `has*()` method naming convention.

```php
use Kassko\DataMapper\Attribute\Getter;

class User
{
    #[Getter(name: 'hasPermission', type: Getter::TYPE_HASER)]
    private ?string $permission = null;
    
    public function hasPermission(): bool
    {
        return $this->permission !== null && $this->permission !== '';
    }
}
```

## Combined Example

```php
use Kassko\DataMapper\Attribute\Getter;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

class User
{
    use LoadableTrait;
    
    #[Getter(name: 'getName', type: Getter::TYPE_GETTER)]
    private ?string $name = null;
    
    #[Getter(name: 'isAdmin', type: Getter::TYPE_ISSER)]
    private bool $admin = false;
    
    #[Getter(name: 'hasEmail', type: Getter::TYPE_HASER)]
    private ?string $email = null;
    
    public function getName(): ?string
    {
        $this->loadProperty('name');
        return $this->name;
    }
    
    public function isAdmin(): bool
    {
        $this->loadProperty('admin');
        return $this->admin === true;
    }
    
    public function hasEmail(): bool
    {
        $this->loadProperty('email');
        return $this->email !== null && $this->email !== '';
    }
}
```

## Auto-Detection (Without Attribute)

When no `#[Getter]` attribute is present, DataMapper uses conventional naming:
- `get{PropertyName}()` for standard getters
- `is{PropertyName}()` for boolean properties
- `has{PropertyName}()` for existence checks

## See Also

- [Setter](Setter.md)
- [Param](Param.md)
