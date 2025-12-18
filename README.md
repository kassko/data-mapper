# PHP 8 Data Mapper Library

A minimal PHP 8 data-mapper library with lazy loading, attributes, and PSR-11 container integration.

## Origin

This project was initiated as a personal open-source initiative, developed independently and outside of any professional assignment.

It is not affiliated with, nor owned by, any organization.

[Read more about the project background](./ABOUT.md)

## Features

- **PHP 8 Attributes**: Use native PHP 8 attributes for metadata (no external annotation library)
- **Lazy Loading**: Properties are loaded on-demand when accessed
- **Single Call Optimization**: Properties sharing the same DataSource configuration are loaded together in one call
- **Service Locator Pattern**: Resolve DataSources via PSR-11 ContainerInterface or direct instantiation
- **WeakMap Registry**: Efficient memory management with PHP 8's WeakMap for tracking loaded properties

## Requirements

- PHP >= 8.0
- PSR-11 Container Interface

## Installation

```bash
composer require kassko/data-mapper-experimental
```

## Usage

### Basic Example

```php
use Kassko\DataMapper\Attribute\DataSource;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;
use Kassko\DataMapper\DataMapper;

// Define your entity
class Person
{
    use LoadableTrait;

    private int $id;

    #[DataSource(class: PersonDataSource::class, method: 'getData', args: ['#id'])]
    private ?string $name = null;

    #[DataSource(class: PersonDataSource::class, method: 'getData', args: ['#id'])]
    private ?string $email = null;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
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
}

// Define your data source
class PersonDataSource
{
    public function getData(int $id): array
    {
        return match($id) {
            1 => ['name' => 'foo', 'email' => 'foo@aaa.com'],
            2 => ['name' => 'bar', 'email' => 'bar@bbb.com'],
            default => ['name' => 'baz', 'email' => 'baz@ccc.com'],
        };
    }
}

// Use the data mapper
$dataMapper = new DataMapper();
$person = new Person(1);
$dataMapper->prepare($person);

echo $person->getName();  // Output: foo (loads both name and email in one call)
echo $person->getEmail(); // Output: foo@aaa.com (already loaded, no additional call)
```

### Service Locator Pattern

You can use the `@` prefix to resolve DataSources from a PSR-11 container:

```php
use Psr\Container\ContainerInterface;

// Configure your container
$container = /* your PSR-11 container */;

// Use service identifier in the attribute
class Person
{
    use LoadableTrait;

    private int $id;

    #[DataSource(class: '@person.data_source', method: 'getData', args: ['#id'])]
    private ?string $name = null;

    // ... rest of the class
}

// Pass container to DataMapper
$dataMapper = new DataMapper($container);
$person = new Person(1);
$dataMapper->prepare($person);
```

### Property References

The `args` parameter in the `#[DataSource]` attribute can reference object properties using the `#` prefix:

```php
#[DataSource(class: PersonDataSource::class, method: 'getData', args: ['#id', 'some-static-value'])]
private ?string $name = null;
```

## How It Works

1. **Selective Hydration**: Only properties with the `#[DataSource]` attribute are hydrated
2. **Lazy Loading**: Properties are loaded when `loadProperty()` is called (typically in getters)
3. **Single Call Optimization**: Properties with identical DataSource signatures (class + method + resolved args) are loaded together
4. **Registry**: A WeakMap tracks which properties have been loaded to avoid duplicate calls

## Testing

```bash
composer install
vendor/bin/phpunit
```

## License

MIT