# LoadableTrait

Provides property locking and lazy loading functionality for objects.

## Overview

`LoadableTrait` adds methods to:
- Lock/unlock properties to prevent lazy/eager loading from modifying their values
- Load properties on-demand
- Load all eager properties

## Usage

```php
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

class Person
{
    use LoadableTrait;
    
    private ?string $firstName = null;
    
    public function setFirstName(string $value): void
    {
        $this->firstName = $value;
        $this->lockProperty('firstName');  // Prevent lazy loading from overwriting
    }
    
    public function resetFirstName(): void
    {
        $this->unlockProperty('firstName');  // Allow lazy loading again
        $this->firstName = null;
    }
}
```

## Methods

### lockProperty(string $propertyName): void

Locks a property to prevent lazy/eager loading from modifying its value.

```php
protected function lockProperty(string $propertyName): void
```

**Use cases:**
- Prevent data source from overwriting manually-set values
- Freeze properties after validation
- Protect sensitive data from being reloaded

**Example:**
```php
public function setManualValue(string $value): void
{
    $this->property = $value;
    $this->lockProperty('property');  // Lock it
}
```

### unlockProperty(string $propertyName): void

Unlocks a property to allow lazy/eager loading to modify its value.

```php
protected function unlockProperty(string $propertyName): void
```

**Example:**
```php
public function reset(): void
{
    $this->unlockProperty('property');  // Unlock
    $this->property = null;
}
```

### isPropertyLocked(string $propertyName): bool

Checks if a property is currently locked.

```php
public function isPropertyLocked(string $propertyName): bool
```

**Returns:** `true` if locked, `false` otherwise

**Example:**
```php
if ($this->isPropertyLocked('property')) {
    // Property is locked
}
```

### loadProperty(string $propertyName): void

Loads a property on-demand using the global LazyLoader (if configured).

```php
protected function loadProperty(string $propertyName): void
```

**Note:** Automatically called by getters in lazy loading scenarios.

### loadEagerProperties(): void

Loads all properties marked with `Loading::TYPE_EAGER`.

```php
public function loadEagerProperties(): void
```

**Example:**
```php
$person = new Person();
$person->loadEagerProperties();  // Load all eager properties
```

## Property Locking in Child Classes

Lock methods are `protected`, allowing child classes to lock properties from parent classes:

```php
class Person
{
    use LoadableTrait;
    
    protected ?string $firstName = null;
    protected ?string $lastName = null;
}

class Employee extends Person
{
    public function freezeAllData(): void
    {
        $this->lockProperty('firstName');  // Lock parent property
        $this->lockProperty('lastName');
    }
}
```

## How Locking Works

When a property is locked:

1. The property name is stored in an internal `$lockedProperties` array
2. When `LazyLoader` attempts to hydrate the property, it checks `isPropertyLocked()`
3. If locked, the property is skipped (not hydrated)
4. Manual setter calls still work - locking only affects lazy/eager loading

## Complete Example

```php
use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\Attribute\MultiPropDataSource;
use Kassko\DataMapper\Attribute\DataSourceRef;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

#[DataSourcesStore([
    new MultiPropDataSource(id: 'personData', class: PersonRepository::class, method: 'find'),
])]
class Person
{
    use LoadableTrait;
    
    #[DataSourceRef(id: 'personData')]
    private ?string $firstName = null;
    
    #[DataSourceRef(id: 'personData')]
    private ?string $lastName = null;
    
    public function setFirstName(string $value): void
    {
        $this->firstName = $value;
        $this->lockProperty('firstName');  // Prevent reload
    }
    
    public function getFirstName(): ?string
    {
        $this->loadProperty('firstName');  // Try to load (blocked if locked)
        return $this->firstName;
    }
    
    public function unlock(): void
    {
        $this->unlockProperty('firstName');
        $this->unlockProperty('lastName');
    }
}

// Usage
$person = new Person();
$person->setFirstName('Manual');  // Sets and locks
$name = $person->getFirstName();  // Returns 'Manual' (not reloaded from data source)

$person->unlock();  // Unlock properties
$name = $person->getFirstName();  // Now loads from data source
```

## See Also

- [DataSource](../attributes/DataSource.md)
- [Loading](../attributes/Loading.md)
- [LazyLoader](../advanced/LazyLoader.md)
