# Summary: Portable Integration Tests Infrastructure

**Date:** 2026-01-03
**Branch:** `improv/tests/CoreTestsPortability`

## Objective

Create an infrastructure that allows Core library integration tests to be reused in the Symfony Bundle context, enabling end-to-end testing from semantic configuration to hydration.

## Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    PortableIntegrationTestCase              │
│  ┌─────────────────────────────────────────────────────┐   │
│  │              DataMapperProviderInterface             │   │
│  │  - getDataMapper(services, config)                   │   │
│  │  - getContainer()                                    │   │
│  │  - tearDown()                                        │   │
│  │  - getName()                                         │   │
│  └─────────────────────────────────────────────────────┘   │
│            ▲                              ▲                 │
│            │                              │                 │
│  ┌─────────┴──────────┐      ┌───────────┴────────────┐    │
│  │ NativeDataMapper   │      │ SymfonyDataMapper      │    │
│  │ Provider           │      │ Provider (in Bundle)   │    │
│  │                    │      │                        │    │
│  │ Uses:              │      │ Uses:                  │    │
│  │ - DataMapperBuilder│      │ - TestKernel           │    │
│  │ - ArrayServiceLocator│    │ - Container            │    │
│  └────────────────────┘      └────────────────────────┘    │
└─────────────────────────────────────────────────────────────┘
```

## Key Components

### DataMapperProviderInterface
Abstract interface that defines how to obtain a DataMapper instance. This allows the same test code to run with different implementations.

### NativeDataMapperProvider
Uses `DataMapperBuilder` to create DataMapper instances directly in PHP, without any framework overhead.

### PortableIntegrationTestCase
Base test class that:
- Manages provider lifecycle
- Provides helper methods for context detection
- Handles fixture autoloading via `LocalFixtureAutoloadTrait`

### LocalFixtureAutoloadTrait Enhancement
Modified to support class inheritance by walking up the parent class hierarchy to find fixtures.

## Benefits

1. **Code Reuse**: Write tests once, run in multiple contexts
2. **End-to-End Coverage**: Test the full path from configuration to hydration
3. **Bundle Validation**: Ensure Bundle correctly integrates with Core
4. **Isolation**: Each provider manages its own lifecycle

## Files Changed

### New Files
- `tests/TestHelpers/DataMapperProviderInterface.php`
- `tests/TestHelpers/NativeDataMapperProvider.php`
- `tests/TestHelpers/PortableIntegrationTestCase.php`
- `tests/Integration/Portable/DataMapperPortableTest.php`
- `tests/Integration/Portable/LazyLoadingPortableTest.php`
- `tests/Integration/Portable/ServiceResolutionPortableTest.php`
- `tests/Integration/Portable/Fixtures/Person.php`
- `tests/Integration/Portable/Fixtures/PersonDataSource.php`
- `tests/Integration/Portable/Fixtures/User.php`
- `tests/Integration/Portable/Fixtures/UserDataSource.php`

### Modified Files
- `tests/TestHelpers/LocalFixtureAutoloadTrait.php` - Added parent class traversal
- `src/DataMapperBuilder.php` - Renamed `addLocator` → `addServiceLocator`
- `src/DataMapper.php` - Added `ensureLoaderRegistered()` method

## Breaking Changes

| Change | Migration |
|--------|-----------|
| `addLocator()` → `addServiceLocator()` | Search and replace in your code |

## Test Coverage

| Test Suite | Tests | Assertions | Status |
|------------|-------|------------|--------|
| Core (all) | 321 | 740 | ✅ Pass |
| Portable (native) | 15 | 28 | ✅ Pass |
