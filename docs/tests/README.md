# Testing Documentation

This document describes the testing strategy, structure, and conventions used in the Data Mapper library.

## Test Categories

The test suite is organized into two main categories:

### Unit Tests (`tests/Unit/`)

Unit tests focus on testing individual classes and methods in isolation. They verify that specific components work correctly without dependencies on external systems.

**Characteristics:**
- Fast execution
- No external dependencies
- Isolated from other components
- Test single responsibilities

### Integration Tests (`tests/Integration/`)

Integration tests verify that multiple components work together correctly. They test real-world scenarios including attribute processing, lazy loading, and hydration.

**Characteristics:**
- Test complete workflows
- Verify attribute behavior
- Test service resolution
- Validate hydration patterns

## Integration Test Structure

### Organization by Category

Integration tests are organized into logical categories:

```
tests/Integration/
├── Attributes/          # PHP 8 attribute tests
├── Classes/             # Class-level integration tests
├── Features/            # Feature-specific tests
├── Portable/            # Cross-environment portable tests
├── Traits/              # Trait behavior tests
└── ...
```

### Attribute Tests (`tests/Integration/Attributes/`)

Each PHP 8 attribute has its own test directory:

```
tests/Integration/Attributes/
├── Context/
├── CustomHydrator/
├── DataSource/
├── DataSourceStore/
├── Hook/
├── MultiPropDataSource/
├── PropertyHydratingHook/
├── PropertyInstantiatingHook/
├── SinglePropDataSource/
└── ...
```

#### Naming Conventions

| Pattern | Usage |
|---------|-------|
| `<AttributeName>/` | Main attribute test directory |
| `<AttributeName>Check<SubTopic>/` | Sub-topic test directory |
| `Fixtures/` | Test fixture classes |

#### Directory Structure

Each attribute test directory typically contains:

```
<AttributeName>/
├── <AttributeName>Test.php       # Main test class
└── Fixtures/                     # Fixture classes
    ├── Entity.php
    ├── EntityDataSource.php
    └── ...
```

## Test Conventions

### Fixture Namespaces

Fixtures use the `Kassko\Sample\<AttributeName>\` namespace pattern:

```php
namespace Kassko\Sample\DataSource;

class User
{
    // ...
}
```

### LocalFixtureAutoloadTrait

Tests use `LocalFixtureAutoloadTrait` to automatically load fixtures from the local `Fixtures/` directory:

```php
use Kassko\DataMapper\Tests\TestHelpers\LocalFixtureAutoloadTrait;

class MyTest extends TestCase
{
    use LocalFixtureAutoloadTrait;
}
```

### LoaderRegistry Cleanup

Always clear the `LoaderRegistry` in `tearDown()` to prevent test pollution:

```php
protected function tearDown(): void
{
    LoaderRegistry::clear();
}
```

### DataMapper Initialization

Create a `DataMapper` instance with `ServiceResolver` before testing:

```php
public function testSomething(): void
{
    new DataMapper(new ServiceResolver());
    
    $entity = new MyEntity(1);
    // ...
}
```

## Portable Tests

Portable tests are designed to run in both native PHP and Symfony Bundle contexts:

```
tests/Integration/Portable/
├── DataMapperPortableTest.php
├── LazyLoadingPortableTest.php
├── ServiceResolutionPortableTest.php
└── Fixtures/
```

### PortableIntegrationTestCase

Extend `PortableIntegrationTestCase` for cross-environment tests:

```php
use Kassko\DataMapper\Tests\TestHelpers\PortableIntegrationTestCase;

class MyPortableTest extends PortableIntegrationTestCase
{
    public function testSomething(): void
    {
        $dataMapper = $this->getDataMapper();
        // ...
    }
}
```

### Context Detection

Use helper methods to detect the execution context:

```php
if ($this->isSymfonyContext()) {
    // Symfony-specific test
}

$this->requiresSymfonyContext('reason');
```

## Running Tests

### All Tests

```bash
./vendor/bin/phpunit
```

### Specific Category

```bash
./vendor/bin/phpunit tests/Integration/Attributes
```

### Single Attribute

```bash
./vendor/bin/phpunit tests/Integration/Attributes/DataSource
```

### With TestDox Output

```bash
./vendor/bin/phpunit --testdox
```

## Best Practices

1. **Isolate fixtures**: Each test directory has its own fixtures
2. **Clear registries**: Always clean up in `tearDown()`
3. **Test one concept**: Each test method should verify one behavior
4. **Use descriptive names**: Test method names should describe the scenario
5. **Document complex tests**: Add comments for non-obvious test logic

## Adding New Attribute Tests

1. Create directory: `tests/Integration/Attributes/<AttributeName>/`
2. Create `Fixtures/` subdirectory
3. Add fixture classes with `Kassko\Sample\<AttributeName>\` namespace
4. Create `<AttributeName>Test.php` with:
   - `LocalFixtureAutoloadTrait`
   - `tearDown()` with `LoaderRegistry::clear()`
   - Test methods covering attribute functionality
