# DataMapperBuilder

A fluent builder for configuring and creating `DataMapper` instances.

## Overview

The `DataMapperBuilder` provides a fluent API for configuring all aspects of the DataMapper:

- Service resolution (containers, locators, factories)
- Custom hydrators
- Caching
- Logging
- Sensitive data handling

## Basic Usage

```php
use Kassko\DataMapper\DataMapperBuilder;

$builder = new DataMapperBuilder();
$dataMapper = $builder->build();
```

## Configuration Methods

### Container & Service Locators

#### `setContainer(ContainerInterface $container): self`

Sets the PSR-11 container for service resolution.

```php
use Psr\Container\ContainerInterface;

$builder->setContainer($container);
```

#### `addLocator(ServiceLocatorInterface $locator): self`

Adds a custom service locator for resolving services.

```php
use Kassko\DataMapper\ArrayServiceLocator;

$builder->addLocator(new ArrayServiceLocator([
    'user.datasource' => UserDataSource::class,
    'address.datasource' => AddressDataSource::class,
]));
```

### Factory Services

#### `addFactoryService(object $factoryService, string $method): self`

Adds a factory service that can create instances. The factory method receives the service key as argument.

```php
class ServiceFactory
{
    public function create(string $key): object
    {
        return match ($key) {
            'user.datasource' => new UserDataSource(),
            default => throw new \Exception("Unknown service: $key"),
        };
    }
}

$builder->addFactoryService(new ServiceFactory(), 'create');
```

#### `addStaticFactory(string|object $factoryClass, string $method): self`

Adds a static factory (class with static method) that can create instances.

```php
class StaticFactory
{
    public static function create(string $key): object
    {
        return match ($key) {
            'user.datasource' => new UserDataSource(),
            default => throw new \Exception("Unknown service: $key"),
        };
    }
}

$builder->addStaticFactory(StaticFactory::class, 'create');
```

#### `addCallable(callable $callable): self`

Adds a callable that can create instances. Supports all callable types.

```php
// Using a closure
$builder->addCallable(fn(string $key) => match ($key) {
    'user.datasource' => new UserDataSource(),
    default => null,
});

// Using array callable
$builder->addCallable([MyFactory::class, 'create']);

// Using string callable
$builder->addCallable('MyFactory::create');
```

### Custom Hydrators

#### `addCustomHydrator(string $key, callable $callable): self`

Adds a custom hydrator for special object creation logic.

```php
$builder->addCustomHydrator('person_hydrator', function (array $data): Person {
    $person = new Person();
    $person->firstName = $data['first_name'] ?? '';
    $person->lastName = $data['last_name'] ?? '';
    return $person;
});
```

Usage in attributes:

```php
use Kassko\DataMapper\Attribute\CustomHydrator;
use Kassko\DataMapper\Attribute\Property;

class Team
{
    #[Property]
    public string $name;
    
    #[Property]
    #[CustomHydrator(key: 'person_hydrator')]
    public Person $leader;
}
```

### Caching & Logging

#### `setCache(CacheInterface $cache): self`

Sets the PSR-16 simple cache for caching metadata.

```php
use Psr\SimpleCache\CacheInterface;

$builder->setCache($cache);
```

#### `setLogger(LoggerInterface $logger): self`

Sets the PSR-3 logger for debug logging.

```php
use Psr\Log\LoggerInterface;

$builder->setLogger($logger);
```

### Sensitive Data Handling

Control how sensitive data is displayed in lineage collection.

#### `setDefaultSensitiveLevel(SensitiveLevel $level): self`

Sets the default sensitive level for all properties.

```php
use Kassko\DataMapper\Enum\SensitiveLevel;

$builder->setDefaultSensitiveLevel(SensitiveLevel::MASK);
```

#### `setSensitiveKeys(array $sensitiveKeys): self`

Sets all sensitive keys at once. Replaces any existing configuration.

```php
use Kassko\DataMapper\Enum\SensitiveLevel;

$builder->setSensitiveKeys([
    'password' => SensitiveLevel::HIDE,
    'ssn' => SensitiveLevel::MASK,
    '*secret*' => SensitiveLevel::HIDE,  // Pattern matching
    'User.email' => SensitiveLevel::MASK,  // Class-specific
]);
```

#### `addSensitiveKey(string $key, SensitiveLevel $level): self`

Adds a single sensitive key.

```php
$builder->addSensitiveKey('password', SensitiveLevel::HIDE);
```

#### `removeSensitiveKeys(string ...$keys): self`

Removes one or more sensitive keys.

```php
$builder->removeSensitiveKeys('password', 'ssn');
```

#### `getSensitiveKeys(): array`

Gets the configured sensitive keys.

```php
$keys = $builder->getSensitiveKeys();
// ['password' => SensitiveLevel::HIDE, ...]
```

#### `getDefaultSensitiveLevel(): SensitiveLevel`

Gets the default sensitive level.

```php
$level = $builder->getDefaultSensitiveLevel();
```

### Building

#### `build(): DataMapper`

Builds and returns a configured `DataMapper` instance.

```php
$dataMapper = $builder->build();
```

## Sensitive Levels

The `SensitiveLevel` enum provides four levels:

| Level | Description | Example |
|-------|-------------|---------|
| `SHOW` | Display full value (default) | `"mysecretpassword"` |
| `HIDE` | Replace with `[SENSITIVE]` | `"[SENSITIVE]"` |
| `MASK` | Show first 2 and last 2 chars | `"my**********rd"` |
| `TYPE_ONLY` | Show only the type | `"[string(16)]"` |

### Pattern Matching

Sensitive key patterns support wildcards:

- `password` - Exact match
- `*password*` - Contains "password"
- `password*` - Starts with "password"
- `*password` - Ends with "password"
- `User.*` - All properties of User class

## Complete Example

```php
use Kassko\DataMapper\DataMapperBuilder;
use Kassko\DataMapper\Enum\SensitiveLevel;
use Kassko\DataMapper\ArrayServiceLocator;

$builder = new DataMapperBuilder();

$dataMapper = $builder
    // Service resolution
    ->setContainer($container)
    ->addLocator(new ArrayServiceLocator([
        'user.datasource' => UserDataSource::class,
        'address.datasource' => AddressDataSource::class,
    ]))
    
    // Factories
    ->addCallable(fn(string $key) => match ($key) {
        'custom.service' => new CustomService(),
        default => null,
    })
    
    // Custom hydrators
    ->addCustomHydrator('person_hydrator', function (array $data): Person {
        return Person::fromArray($data);
    })
    
    // Caching and logging
    ->setCache($cache)
    ->setLogger($logger)
    
    // Sensitive data handling
    ->setDefaultSensitiveLevel(SensitiveLevel::SHOW)
    ->addSensitiveKey('password', SensitiveLevel::HIDE)
    ->addSensitiveKey('*token*', SensitiveLevel::MASK)
    ->addSensitiveKey('ssn', SensitiveLevel::MASK)
    
    // Build
    ->build();

// Enable lineage collection for debugging
$dataMapper->enableLineageCollection();

// Use the DataMapper
$hydrator = $dataMapper->getHydrator();
$user = $hydrator->hydrate(User::class, $userData);

// Check collected events (sensitive values are protected)
$events = $dataMapper->getLineageCollector()->getEventsAsArrays();
```

## See Also

- [Hydrator](Hydrator.md) - For hydrating objects from raw data
- [DataLineageCollector](DataLineageCollector.md) - For debugging hydration
- [ArrayServiceLocator](../classes/ArrayServiceLocator.md) - Simple service locator
