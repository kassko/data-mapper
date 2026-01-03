# Hydrator

A clean, simple interface for hydrating objects from raw data.

## Overview

The `Hydrator` class provides a convenient API for creating and populating objects from associative arrays. It wraps the internal `Loader` functionality and handles:

- Object instantiation with `#[Param]` attribute support
- Execution of `#[PropertyInstantiatingHook]` hooks
- Property hydration from raw data

## Usage

### Getting the Hydrator

```php
use Kassko\DataMapper\DataMapperBuilder;

$builder = new DataMapperBuilder();
$dataMapper = $builder->build();

$hydrator = $dataMapper->getHydrator();
```

### Basic Hydration

```php
// Create and hydrate a new object from raw data
$person = $hydrator->hydrate(Person::class, [
    'first_name' => 'John',
    'last_name' => 'Doe',
    'email' => 'john.doe@example.com',
]);

echo $person->firstName; // "John"
```

### Hydrating Existing Objects

```php
// Create an object first
$person = new Person();

// Hydrate it with raw data
$hydrator->hydrateExisting($person, [
    'first_name' => 'Jane',
    'last_name' => 'Smith',
]);

echo $person->firstName; // "Jane"
```

## Methods

### `hydrate(string $className, array $rawData): object`

Creates a new instance of the specified class and hydrates its properties from the provided raw data array.

**Parameters:**
- `$className` - The fully qualified class name to instantiate
- `$rawData` - An associative array of raw data to hydrate the object with

**Returns:** The hydrated object instance

**Throws:**
- `\InvalidArgumentException` if the class cannot be instantiated
- `\ReflectionException` if reflection fails

```php
$user = $hydrator->hydrate(User::class, [
    'username' => 'johndoe',
    'email' => 'john@example.com',
]);
```

### `hydrateExisting(object $object, array $rawData): object`

Hydrates the properties of an existing object instance from the provided raw data array.

**Parameters:**
- `$object` - The object instance to hydrate
- `$rawData` - An associative array of raw data to hydrate the object with

**Returns:** The same object instance, now hydrated

```php
$user = new User();
$hydrator->hydrateExisting($user, [
    'username' => 'johndoe',
]);
```

## Lifecycle

When using `hydrate()`, the following steps occur:

1. **Instantiation** - The object is created using `#[Param]` attributes if present
2. **Instantiating Hooks** - `#[PropertyInstantiatingHook]` hooks are executed
3. **Hydration** - Properties are hydrated from the raw data

When using `hydrateExisting()`:

1. **Instantiating Hooks** - `#[PropertyInstantiatingHook]` hooks are executed
2. **Hydration** - Properties are hydrated from the raw data

## Example with Attributes

```php
use Kassko\DataMapper\Attribute\Property;
use Kassko\DataMapper\Attribute\PropertyInstantiatingHook;

class Person
{
    #[Property(key: 'first_name')]
    public string $firstName;
    
    #[Property(key: 'last_name')]
    public string $lastName;
    
    public ?string $fullName = null;
    
    #[PropertyInstantiatingHook]
    public function onInstantiate(array $rawData): void
    {
        // Called before hydration
        $this->fullName = ($rawData['first_name'] ?? '') . ' ' . ($rawData['last_name'] ?? '');
    }
}

$person = $hydrator->hydrate(Person::class, [
    'first_name' => 'John',
    'last_name' => 'Doe',
]);

echo $person->fullName; // "John Doe"
```

## Nested Object Hydration

The hydrator supports nested object hydration when the `class` parameter is specified:

```php
use Kassko\DataMapper\Attribute\Property;

class User
{
    #[Property]
    public string $name;
    
    #[Property(class: Address::class)]
    public Address $address;
}

class Address
{
    #[Property]
    public string $street;
    
    #[Property]
    public string $city;
}

$user = $hydrator->hydrate(User::class, [
    'name' => 'John',
    'address' => [
        'street' => '123 Main St',
        'city' => 'New York',
    ],
]);

echo $user->address->city; // "New York"
```

## See Also

- [DataMapperBuilder](DataMapperBuilder.md) - For building and configuring the DataMapper
- [Property](../attributes/Property.md) - Property attribute documentation
- [PropertyInstantiatingHook](../attributes/PropertyInstantiatingHook.md) - Instantiating hooks
