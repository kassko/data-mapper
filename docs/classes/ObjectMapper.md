# ObjectMapper

A clean, simple interface for mapping objects from DTO (Data Transfer Object) sources.

## Overview

The `ObjectMapper` class provides a convenient API for mapping domain objects from DTO sources. While the `Hydrator` works with raw data arrays, the `ObjectMapper` works with source objects (DTOs). It wraps the internal `Loader` functionality and handles:

- Object instantiation with `#[Param]` attribute support
- Execution of `#[PropertyInstantiatingHook]` hooks
- Property mapping from source objects
- Deep path resolution (e.g., `address.street.number`)

## Usage

### Getting the ObjectMapper

```php
use Kassko\DataMapper\DataMapperBuilder;

$builder = new DataMapperBuilder();
$dataMapper = $builder->build();

$objectMapper = $dataMapper->getObjectMapper();
```

### Basic Mapping

```php
// Map a DTO to a domain object
$person = $objectMapper->map(Person::class, $personDto);

echo $person->firstName; // Mapped from $personDto->firstName
```

### Mapping to Existing Objects

```php
// Create an object first
$person = new Person();

// Map data from a DTO
$objectMapper->mapToExisting($person, $personDto);

echo $person->firstName; // Mapped from $personDto->firstName
```

## Methods

### `map(string $className, object $sourceObject): object`

Creates a new instance of the specified class and maps its properties from the provided source object.

**Parameters:**
- `$className` - The fully qualified class name to instantiate
- `$sourceObject` - The source object (DTO) to map from

**Returns:** The mapped object instance

**Throws:**
- `\InvalidArgumentException` if the class cannot be instantiated
- `\ReflectionException` if reflection fails

```php
$user = $objectMapper->map(User::class, $userDto);
```

### `mapToExisting(object $object, object $sourceObject): object`

Maps the properties of an existing object instance from the provided source object.

**Parameters:**
- `$object` - The object instance to map to
- `$sourceObject` - The source object (DTO) to map from

**Returns:** The same object instance, now mapped

```php
$user = new User();
$objectMapper->mapToExisting($user, $userDto);
```

## Lifecycle

When using `map()`, the following steps occur:

1. **Instantiation** - The object is created using `#[Param]` attributes if present
2. **Instantiating Hooks** - `#[PropertyInstantiatingHook]` hooks are executed
3. **Mapping** - Properties are mapped from the source object

When using `mapToExisting()`:

1. **Instantiating Hooks** - `#[PropertyInstantiatingHook]` hooks are executed
2. **Mapping** - Properties are mapped from the source object

## Deep Path Mapping

One of the powerful features of `ObjectMapper` is deep path mapping. You can map properties from nested objects in the DTO:

```php
// Source DTO with nested objects
class PersonDto
{
    public string $firstName;
    public string $lastName;
    public ?AddressDto $address = null;
}

class AddressDto
{
    public string $street;
    public string $city;
    public string $postalCode;
}

// Domain object using deep path mapping
use Kassko\DataMapper\Attribute\Property;

class Person
{
    #[Property(sourceField: 'firstName')]
    public string $firstName;
    
    #[Property(sourceField: 'lastName')]
    public string $lastName;
    
    // Deep path: maps from $dto->address->street
    #[Property(sourceField: 'address.street')]
    public ?string $street = null;
    
    // Deep path: maps from $dto->address->city
    #[Property(sourceField: 'address.city')]
    public ?string $city = null;
    
    // Deep path: maps from $dto->address->postalCode
    #[Property(sourceField: 'address.postalCode')]
    public ?string $postalCode = null;
}

// Usage
$addressDto = new AddressDto();
$addressDto->street = '123 Main St';
$addressDto->city = 'New York';
$addressDto->postalCode = '10001';

$personDto = new PersonDto();
$personDto->firstName = 'John';
$personDto->lastName = 'Doe';
$personDto->address = $addressDto;

$person = $objectMapper->map(Person::class, $personDto);

echo $person->street; // "123 Main St"
echo $person->city;   // "New York"
```

Deep paths can have multiple levels: `address.street.number` will traverse `$dto->address->street->number`.

## Hydrator vs ObjectMapper

| Feature | Hydrator | ObjectMapper |
|---------|----------|--------------|
| Input | Array (raw data) | Object (DTO) |
| Use case | API responses, database results | DTO transformations |
| Method | `hydrate()` | `map()` |
| Deep paths | Requires nested arrays | Supports object traversal |

**When to use Hydrator:**
- Working with arrays from APIs, databases, or configuration
- Deserializing JSON/XML responses

**When to use ObjectMapper:**
- Transforming DTOs to domain objects
- Working with objects from external libraries
- Applying domain logic during mapping

## Example with Custom Object Mappers

You can register custom object mappers for complex transformations:

```php
use Kassko\DataMapper\DataMapperBuilder;

$builder = new DataMapperBuilder();

// Register a custom object mapper for addresses
$builder->addCustomObjectMapper('address_mapper', function(object $addressDto, object $context) {
    // Custom transformation logic
    $address = new Address();
    $address->setFullAddress(
        $addressDto->street . ', ' . $addressDto->city . ' ' . $addressDto->postalCode
    );
    return $address;
});

$dataMapper = $builder->build();

// Retrieve the custom mapper later
$addressMapper = $dataMapper->getCustomObjectMapper('address_mapper');
if ($addressMapper !== null) {
    $address = $addressMapper($addressDto, $context);
}
```

## Integration with Hydrator

Both ObjectMapper and Hydrator can be used together. A domain object may have:
- Properties hydrated from raw data arrays (via data sources)
- Properties mapped from DTO sources

```php
$dataMapper = (new DataMapperBuilder())->build();

// Use Hydrator for array data
$hydrator = $dataMapper->getHydrator();
$person1 = $hydrator->hydrate(Person::class, ['first_name' => 'John']);

// Use ObjectMapper for DTO sources
$objectMapper = $dataMapper->getObjectMapper();
$person2 = $objectMapper->map(Person::class, $personDto);
```

## See Also

- [Hydrator](Hydrator.md) - For hydrating from raw data arrays
- [Property](../attributes/Property.md) - Property mapping configuration
- [CustomObjectMapper](../attributes/CustomObjectMapper.md) - Custom object mapper attribute
- [DataMapperBuilder](DataMapperBuilder.md) - Building and configuring DataMapper
