# Pull Request: Fix Deep Path Mapping for Raw Data Hydration

**Branch:** `fix/code/FixAndTest_DeepPathMapping`  
**Date:** 2026-01-10  
**Status:** Ready for Review

## Summary

This PR fixes the deep path mapping feature (e.g., `address.street`) to work correctly with raw data arrays via the `Hydrator` class.

## Problem

The deep path mapping feature using dot notation (e.g., `#[Property(sourceField: 'address.street')]`) was only working when using `ObjectMapper` with DTO sources. When hydrating objects from raw data arrays using the `Hydrator` class, the deep paths were not being resolved - the values remained `null`.

### Root Cause

The `hydrateObject()` method in `Loader.php` was using direct array key access:

```php
if (!array_key_exists($fieldName, $data)) { ... }
$value = $data[$fieldName];
```

This doesn't support dot-separated paths like `address.street` - it looks for a literal key named `address.street` instead of traversing into `$data['address']['street']`.

## Solution

Added two private helper methods to `Loader.php`:

1. **`fieldExistsInData(string $fieldPath, array $data): bool`**  
   Checks if a field exists in a data array, supporting deep paths by traversing nested arrays.

2. **`resolveValueFromData(string $fieldPath, array $data): mixed`**  
   Resolves a value from a data array using deep path notation.

Modified `hydrateObject()` to use these methods instead of direct array access.

## Files Changed

### Modified
- `src/Loader/Loader.php` - Added deep path support to raw data hydration

### Added  
- `tests/Integration/Features/DeepPathMapping/DeepPathMappingTest.php` - 14 integration tests
- `tests/Integration/Features/DeepPathMapping/Fixtures/AddressDto.php`
- `tests/Integration/Features/DeepPathMapping/Fixtures/AddressWithStreetDto.php`
- `tests/Integration/Features/DeepPathMapping/Fixtures/PersonDto.php`
- `tests/Integration/Features/DeepPathMapping/Fixtures/PersonWithDeepAddressDto.php`
- `tests/Integration/Features/DeepPathMapping/Fixtures/PersonWithDeeplyFlattenedAddress.php`
- `tests/Integration/Features/DeepPathMapping/Fixtures/PersonWithFlattenedAddress.php`
- `tests/Integration/Features/DeepPathMapping/Fixtures/StreetDto.php`

## Test Coverage

### DTO Source Tests (ObjectMapper) - Verified still working
- ✅ 2-level nesting (`address.street`)
- ✅ 3-level nesting (`address.street.name`)
- ✅ Null nested objects
- ✅ Partially null paths
- ✅ Mapping to existing objects

### Raw Data Tests (Hydrator) - Now fixed
- ✅ 2-level nesting (`address.street`)
- ✅ 3-level nesting (`address.street.name`)
- ✅ Null nested arrays
- ✅ Missing nested keys
- ✅ Partially missing paths
- ✅ Hydrating existing objects

### Edge Cases
- ✅ Empty paths
- ✅ Empty string values
- ✅ Empty arrays

## Test Results

```
PHPUnit 11.5.46 by Sebastian Bergmann and contributors.

..............                                                    14 / 14 (100%)

Time: 00:00.250, Memory: 10.00 MB

OK, but there were issues!
Tests: 14, Assertions: 68, PHPUnit Deprecations: 1.
```

All 214 integration tests pass with no regressions.

## Breaking Changes

None. This is a bug fix that makes the existing `#[Property(sourceField: 'deep.path')]` attribute work as documented when using the Hydrator.

## Usage Example

```php
class PersonWithFlattenedAddress
{
    #[Property(sourceField: 'firstName')]
    private ?string $firstName = null;

    // Deep path: maps from address.street in the raw data
    #[Property(sourceField: 'address.street')]
    private ?string $street = null;

    #[Property(sourceField: 'address.city')]
    private ?string $city = null;
}

// Now works correctly:
$rawData = [
    'firstName' => 'Alice',
    'address' => [
        'street' => '456 Oak Avenue',
        'city' => 'Boston',
    ],
];

$hydrator = $dataMapper->getHydrator();
$person = $hydrator->hydrate(PersonWithFlattenedAddress::class, $rawData);

$person->getStreet(); // Returns '456 Oak Avenue' (was null before fix)
$person->getCity();   // Returns 'Boston' (was null before fix)
```
