# PR: Portable Integration Tests Infrastructure

**Date:** 2026-01-03
**Branch:** `improv/tests/CoreTestsPortability`

## Summary

This PR introduces an infrastructure for portable integration tests that can be executed both in native PHP context and within a Symfony Bundle context. This allows Core library tests to be reused by the `data-mapper-bundle` to verify proper integration.

## Changes

### New Files

#### `tests/TestHelpers/DataMapperProviderInterface.php`
Interface that abstracts DataMapper creation across different environments:
- `getDataMapper(array $services, array $config)`: Get or create a DataMapper instance
- `getContainer()`: Get the underlying service container (if available)
- `tearDown()`: Clean up after tests
- `getName()`: Get provider name for debugging

#### `tests/TestHelpers/NativeDataMapperProvider.php`
Native PHP implementation of `DataMapperProviderInterface`:
- Uses `DataMapperBuilder` to create DataMapper instances
- Registers services via `ArrayServiceLocator`
- Supports custom hydrators and configuration options

#### `tests/TestHelpers/PortableIntegrationTestCase.php`
Abstract base class for portable integration tests:
- Uses `LocalFixtureAutoloadTrait` for dynamic fixture loading
- Provider injection mechanism via `setDataMapperProvider()`
- Helper methods: `isSymfonyContext()`, `isNativeContext()`, `requiresSymfonyContext()`, `requiresNativeContext()`
- Automatic cleanup via `tearDown()`

#### `tests/TestHelpers/LocalFixtureAutoloadTrait.php` (Modified)
Enhanced to support inherited test classes:
- New `getFixturesDirectory()` method that walks up the class hierarchy
- Finds fixtures in parent class directories when not found in child class

#### `tests/Integration/Portable/` Directory
New directory containing portable tests:
- `DataMapperPortableTest.php`: Basic DataMapper functionality tests
- `LazyLoadingPortableTest.php`: Lazy loading tests
- `ServiceResolutionPortableTest.php`: Service resolution tests
- `Fixtures/`: Local fixture classes (Person, PersonDataSource, User, UserDataSource)

### Modified Files

#### `src/DataMapperBuilder.php`
- Renamed `addLocator()` to `addServiceLocator()` for better clarity

#### `src/DataMapper.php`
- Added `ensureLoaderRegistered()` method to re-register the loader in the LoaderRegistry when needed (useful when registry is cleared between tests but DataMapper instance is reused)

### API Changes

| Old Method | New Method |
|------------|------------|
| `DataMapperBuilder::addLocator()` | `DataMapperBuilder::addServiceLocator()` |

## Test Results

- **Core Tests:** 321 tests, 740 assertions, all passing
- **Portable Tests:** 15 tests (12 native + 3 Symfony-specific), all passing

## Usage

### Running Portable Tests in Native Context

```bash
cd data-mapper
./vendor/bin/phpunit tests/Integration/Portable
```

### Creating New Portable Tests

```php
use Kassko\DataMapper\Tests\TestHelpers\PortableIntegrationTestCase;

class MyPortableTest extends PortableIntegrationTestCase
{
    public function testSomething(): void
    {
        $dataMapper = $this->getDataMapper(['my.service' => new MyService()]);
        
        // Test with DataMapper...
        
        if ($this->isSymfonyContext()) {
            // Symfony-specific assertions
            $container = $this->getContainer();
        }
    }
}
```

## Related PRs

- `data-mapper-bundle`: PR for running these tests in Symfony context
