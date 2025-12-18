# DataMapper - New Features Examples

This document demonstrates the new features added to the data-mapper library.

## 1. DataSourcesStore Attribute

Group multiple DataSource definitions in one place at the class level:

```php
use Kassko\DataMapper\Attribute\DataSource;
use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\Attribute\DataSourceRef;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

#[DataSourcesStore([
    new DataSource(
        id: 'personSource',
        class: PersonDataSource::class,
        method: 'getData',
        args: ['#id'],
        supplySeveralFields: true
    ),
    new DataSource(
        id: 'carSource',
        class: CarRepository::class,
        method: 'find',
        args: ["expr(source('personSource')['car_id'])"]
    )
])]
class Person
{
    use LoadableTrait;

    private int $id;

    #[DataSourceRef(id: 'personSource')]
    private ?string $name = null;

    #[DataSourceRef(id: 'carSource')]
    private ?Car $car = null;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function getName(): ?string
    {
        $this->loadProperty('name');
        return $this->name;
    }

    public function getCar(): ?Car
    {
        $this->loadProperty('car');
        return $this->car;
    }
}
```

## 2. DataSourceRef Attribute

Reference a DataSource by its id from the store:

```php
#[DataSourceRef(id: 'personSource')]
private ?string $name = null;
```

Instead of repeating the DataSource configuration on each property, you define it once in the store and reference it by id.

## 3. Field Attribute

Map a property to a different key name in the returned data:

```php
use Kassko\DataMapper\Attribute\Field;

#[DataSourceRef(id: 'personSource')]
#[Field(name: 'first_name')]
private ?string $firstName = null;
```

When PersonDataSource returns `['first_name' => 'John', ...]`, the value 'John' will be mapped to the `$firstName` property.

## 4. supplySeveralFields Option

Control how DataSources hydrate properties:

### supplySeveralFields = false (default)

Each property triggers a separate call, or properties with identical signatures share a single call:

```php
class PersonDataSource
{
    public function getData(int $id): array
    {
        return ['name' => 'John', 'email' => 'john@example.com'];
    }
}

class Person
{
    #[DataSource(class: PersonDataSource::class, method: 'getData', args: ['#id'])]
    private ?string $name = null;

    #[DataSource(class: PersonDataSource::class, method: 'getData', args: ['#id'])]
    private ?string $email = null;
}
```

Both properties have the same signature, so only ONE call to `getData()` is made, and both are hydrated.

### supplySeveralFields = true

ALL properties with `#[DataSourceRef]` pointing to this source are hydrated in a SINGLE call:

```php
#[DataSourcesStore([
    new DataSource(
        id: 'personSource',
        class: PersonDataSource::class,
        method: 'getData',
        args: ['#id'],
        supplySeveralFields: true  // ← Key difference
    )
])]
class Person
{
    #[DataSourceRef(id: 'personSource')]
    private ?string $name = null;

    #[DataSourceRef(id: 'personSource')]
    #[Field(name: 'first_name')]
    private ?string $firstName = null;

    #[DataSourceRef(id: 'personSource')]
    private ?string $email = null;

    // This property is NOT hydrated (no DataSourceRef)
    private ?int $age = null;
}
```

When ANY of the properties with `DataSourceRef(id: 'personSource')` is accessed, the DataSource is called ONCE, and ALL three properties are hydrated simultaneously.

## 5. Expression Language

### Simple Property References

Use `#propertyName` to reference property values:

```php
new DataSource(
    id: 'personSource',
    class: PersonDataSource::class,
    method: 'getData',
    args: ['#id']  // Passes the value of $this->id
)
```

### Advanced Expressions with expr()

Use `expr(source('id')['key'])` to execute a DataSource and extract values:

```php
new DataSource(
    id: 'carSource',
    class: CarRepository::class,
    method: 'find',
    args: ["expr(source('personSource')['car_id'])"]
    // 1. Executes personSource DataSource
    // 2. Extracts the 'car_id' key from the result
    // 3. Passes it to CarRepository::find()
)
```

### Dependency Chain Resolution

The expression language automatically resolves dependencies:

```php
$person = new Person(1);
$dataMapper->prepare($person);

// Accessing car triggers:
// 1. personSource is executed (because car expression depends on it)
// 2. car_id is extracted from personSource result
// 3. carSource is executed with car_id
// 4. Car is loaded and returned
$car = $person->getCar();

// Now accessing name doesn't trigger another call
// because personSource was already loaded when resolving car
$name = $person->getName();
```

### Result Caching

DataSource results are automatically cached per object to avoid duplicate executions:

```php
$person = new Person(1);
$dataMapper->prepare($person);

$car = $person->getCar();        // Loads personSource + carSource
$name = $person->getName();      // Uses cached personSource result
$firstName = $person->getFirstName();  // Uses cached personSource result
```

## Complete Example

```php
<?php

use Kassko\DataMapper\Attribute\DataSource;
use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\Attribute\DataSourceRef;
use Kassko\DataMapper\Attribute\Field;
use Kassko\DataMapper\DataMapper;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

// Data Sources
class PersonDataSource
{
    public function getData(int $id): array
    {
        return [
            'name' => 'John Doe',
            'first_name' => 'John',
            'email' => 'john@example.com',
            'car_id' => 100
        ];
    }
}

class CarRepository
{
    public function find(int $id): ?Car
    {
        return new Car(100, 'Toyota', 'Camry');
    }
}

class Car
{
    public function __construct(
        public readonly int $id,
        public readonly string $brand,
        public readonly string $model
    ) {}
}

// Entity with DataSourcesStore
#[DataSourcesStore([
    new DataSource(
        id: 'personSource',
        class: PersonDataSource::class,
        method: 'getData',
        args: ['#id'],
        supplySeveralFields: true
    ),
    new DataSource(
        id: 'carSource',
        class: CarRepository::class,
        method: 'find',
        args: ["expr(source('personSource')['car_id'])"]
    )
])]
class Person
{
    use LoadableTrait;

    private int $id;

    #[DataSourceRef(id: 'personSource')]
    #[Field(name: 'first_name')]
    private ?string $firstName = null;

    #[DataSourceRef(id: 'personSource')]
    private ?string $name = null;

    #[DataSourceRef(id: 'personSource')]
    private ?string $email = null;

    #[DataSourceRef(id: 'carSource')]
    private ?Car $car = null;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function getFirstName(): ?string
    {
        $this->loadProperty('firstName');
        return $this->firstName;
    }

    public function getName(): ?string
    {
        $this->loadProperty('name');
        return $this->name;
    }

    public function getEmail(): ?string
    {
        $this->loadProperty('email');
        return $this->email;
    }

    public function getCar(): ?Car
    {
        $this->loadProperty('car');
        return $this->car;
    }
}

// Usage
$dataMapper = new DataMapper();
$person = new Person(1);
$dataMapper->prepare($person);

echo $person->getFirstName(); // "John" (loaded from 'first_name' key)
echo $person->getName();      // "John Doe"
echo $person->getEmail();     // "john@example.com"
$car = $person->getCar();     // Car object with Toyota Camry
```

## Key Benefits

1. **Cleaner Code**: Define DataSources once, reference by id
2. **Performance**: supplySeveralFields reduces database calls
3. **Flexibility**: Field mapping allows adapting to different data formats
4. **Power**: Expression language enables complex dependency chains
5. **Efficiency**: Automatic caching prevents duplicate executions
6. **Backward Compatible**: All existing code continues to work
