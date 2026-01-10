# Pull Request: MultiPropDataSource Uniqueness Documentation & Tests

**Branch:** `fix/code/FixAndTestMultiPropDataSourceBehav`  
**Date:** 2026-01-10  
**Status:** Ready for Review

## Summary

This PR updates the documentation to correctly reflect the MultiPropDataSource uniqueness constraint and adds validation tests. The documentation was showing incorrect examples with multiple `#[DataSourceRef(id: 'xxx')]` on different properties pointing to the same MultiPropDataSource id, which is now invalid.

## Problem

The existing documentation showed patterns like:

```php
#[MultiPropDataSource(id: 'personData', ...)]
class Person {
    #[DataSourceRef(id: 'personData')]  // ❌ MULTIPLE references to same id
    private ?string $firstName = null;
    
    #[DataSourceRef(id: 'personData')]  // ❌ NOT ALLOWED
    private ?string $lastName = null;
}
```

This pattern was misleading because:
1. The `MetadataValidator` enforces uniqueness - each MultiPropDataSource id can only be referenced ONCE per class
2. Other properties are meant to be "cross-hydrated" automatically if the returned data contains keys matching their source field mappings

## Solution

### Updated Documentation

All documentation now shows the correct cross-hydration pattern:

```php
#[DataSourcesStore([
    new MultiPropDataSource(
        id: 'personData',
        class: PersonDataSource::class,
        method: 'getData',  // Returns ['firstName' => 'John', 'lastName' => 'Doe']
        args: ['#id']
    ),
])]
class Person {
    // Only ONE property explicitly references the MultiPropDataSource
    #[DataSourceRef(id: 'personData')]
    private ?string $firstName = null;
    
    // Cross-hydrated automatically from 'lastName' key in the returned data
    // NO DataSourceRef needed!
    private ?string $lastName = null;
}
```

### CustomObjectMapper Documentation

Added documentation for the `CustomObjectMapper` attribute (parallel to `CustomHydrator` for DTO-to-domain mapping):

- Added example in README.md basic usage section
- Added new section after CustomHydrator in README.md
- Added `addCustomObjectMapper()` documentation in DataMapperBuilder.md

## Files Changed

### Documentation Updated

| File | Changes |
|------|---------|
| `README.md` | Added CustomObjectMapper in basic usage + new section; Fixed Multi-Property Hydration example |
| `docs/attributes/MultiPropDataSource.md` | Fixed all examples to show single reference + cross-hydration pattern |
| `docs/classes/DataMapperBuilder.md` | Added `addCustomObjectMapper()` method documentation |
| `EXAMPLE.md` | Fixed "Multi-Property Data Source", "Mixed Single and Multi-Property Sources", "Hook with External Service" sections |

### Tests Added

| File | Description |
|------|-------------|
| `tests/Unit/Validation/MultiPropDataSourceUniquenessTest.php` | 6 tests for uniqueness validation |
| `tests/Unit/Validation/Fixtures/ValidSingleSource.php` | Valid: Single property with DataSourceRef |
| `tests/Unit/Validation/Fixtures/ValidMultipleSources.php` | Valid: Multiple sources with different ids |
| `tests/Unit/Validation/Fixtures/ValidWithCrossHydration.php` | Valid: Cross-hydration pattern |
| `tests/Unit/Validation/Fixtures/DuplicateMultiPropOnProperties.php` | Invalid: Same id on multiple properties |
| `tests/Unit/Validation/Fixtures/DuplicateDataSourceRefOnProperties.php` | Invalid: Multiple refs to same id |
| `tests/Unit/Validation/Fixtures/MixedDuplicateViolation.php` | Invalid: Mixed violations |
| `tests/Unit/Validation/Fixtures/PersonDataSource.php` | Mock data source for tests |

## Test Coverage

### Uniqueness Validation Tests - 6 tests

| Test | Description | Expected |
|------|-------------|----------|
| `validSingleSourcePassesValidation` | Single property with DataSourceRef | ✅ Pass |
| `validMultipleSourcesWithDifferentIdsPassesValidation` | Different MultiPropDataSource ids | ✅ Pass |
| `validCrossHydrationPassesValidation` | One ref + cross-hydrated properties | ✅ Pass |
| `duplicateMultiPropDataSourceOnPropertiesFailsValidation` | Same id declared twice on properties | ❌ Fail with error |
| `duplicateDataSourceRefToSameMultiPropFailsValidation` | Multiple refs to same MultiPropDataSource | ❌ Fail with error |
| `mixedViolationsReportMultipleErrors` | Multiple uniqueness violations | ❌ Fail with errors |

## Cross-Hydration Explained

When a property with `#[DataSourceRef(id: 'xxx')]` is loaded:

1. The MultiPropDataSource method is called
2. The returned data (e.g., `['firstName' => 'John', 'lastName' => 'Doe']`) is available
3. Other properties WITHOUT explicit DataSourceRef get hydrated if:
   - The data contains a key matching their `sourceField` mapping
   - OR the data contains a key matching their property name

### Priority Behavior

- Higher `priority` value wins
- If equal priority, the property's own DataSource wins over cross-hydrated values

## Breaking Changes

None. This PR only fixes documentation and adds tests for existing validation behavior.

## Verification

```bash
# Run the new validation tests
cd data-mapper
php vendor/bin/phpunit tests/Unit/Validation/MultiPropDataSourceUniquenessTest.php

# All 6 tests should pass
```
