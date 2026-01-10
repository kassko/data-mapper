# Summary: MultiPropDataSource Uniqueness Documentation & Tests

**Date:** 2026-01-10  
**Branch:** `fix/code/FixAndTestMultiPropDataSourceBehav`

## Objective

Update documentation to correctly reflect the MultiPropDataSource uniqueness constraint and add validation tests for the uniqueness enforcement.

## Changes Made

### 1. CustomObjectMapper Documentation

Added documentation for the `CustomObjectMapper` attribute, which is similar to `CustomHydrator` but for DTO-to-domain object mapping:

- **README.md**: Added example in basic usage section and new section after CustomHydrator
- **DataMapperBuilder.md**: Added `addCustomObjectMapper(string $key, callable $callable)` method documentation

### 2. Fixed Incorrect MultiPropDataSource Examples

The old documentation showed this **incorrect** pattern:

```php
#[MultiPropDataSource(id: 'personData', ...)]
class Person {
    #[DataSourceRef(id: 'personData')]  // Multiple references...
    private ?string $firstName = null;
    
    #[DataSourceRef(id: 'personData')]  // ...to same id: INVALID!
    private ?string $lastName = null;
}
```

Updated to the **correct** cross-hydration pattern:

```php
#[DataSourcesStore([new MultiPropDataSource(id: 'personData', ...)])]
class Person {
    // ONLY ONE reference to the MultiPropDataSource
    #[DataSourceRef(id: 'personData')]
    private ?string $firstName = null;
    
    // Cross-hydrated automatically - no DataSourceRef needed!
    private ?string $lastName = null;
}
```

Files updated:
- `README.md` - Multi-Property Hydration section
- `docs/attributes/MultiPropDataSource.md` - All examples
- `EXAMPLE.md` - 3 sections fixed

### 3. Validation Tests

Created new test suite for MultiPropDataSource uniqueness validation:

- **Location:** `tests/Unit/Validation/MultiPropDataSourceUniquenessTest.php`
- **6 tests** covering valid and invalid patterns
- **7 fixtures** demonstrating correct and incorrect usage

## Key Concept: Cross-Hydration

When a MultiPropDataSource is loaded:
1. The property with `#[DataSourceRef(id: 'xxx')]` triggers the data source call
2. Returned data (e.g., `['firstName' => 'John', 'lastName' => 'Doe']`) is available
3. Other properties **without** explicit DataSourceRef are automatically hydrated if the data contains matching keys

## Test Results

```
PHPUnit 11.5.46
OK, but there were issues!
Tests: 6, Assertions: 16, PHPUnit Deprecations: 7.
```

All 6 tests pass successfully.

## Files Modified

| File | Type |
|------|------|
| `README.md` | Updated |
| `docs/attributes/MultiPropDataSource.md` | Updated |
| `docs/classes/DataMapperBuilder.md` | Updated |
| `EXAMPLE.md` | Updated |
| `tests/Unit/Validation/MultiPropDataSourceUniquenessTest.php` | Created |
| `tests/Unit/Validation/Fixtures/*.php` | Created (7 files) |
| `docs/history/pull-requests/PR-2026_01_10---MultiPropDataSourceUniqueness.md` | Created |
