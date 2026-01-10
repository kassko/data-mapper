# Pull Request: Fix and Extend Deep Path Mapping

**Branch:** `fix/code/FixAndTest_DeepPathMapping`  
**Date:** 2026-01-10  
**Status:** Ready for Review

## Summary

This PR fixes the deep path mapping feature (e.g., `address.street`) and extends it to work with all data sources:
- Raw data arrays (via Hydrator)
- DTO sources (via ObjectMapper) - already working
- MultiPropDataSource (lazy loading)
- SinglePropDataSource (lazy loading)

## Problem

The deep path mapping feature using dot notation (e.g., `#[Property(sourceField: 'address.street')]`) had several limitations:

1. **Raw Data (Hydrator):** Deep paths were not being resolved - values remained `null`
2. **MultiPropDataSource:** Properties with deep paths like `product._keywords` were not matched to the data
3. **SinglePropDataSource:** When the data source returns nested data, deep paths were not extracted

### Root Cause

Multiple methods in `Loader.php` used direct array key access instead of traversing nested structures:
- `hydrateObject()` - for raw data hydration
- `hydrateProperty()` - for DataSource property hydration
- `propertyMatchesDataField()` - for filtering properties based on data availability
- `loadSingleProperty()` - for SinglePropDataSource loading

## Solution

### Helper Methods (Added in first commit)

1. **`fieldExistsInData(string $fieldPath, array $data): bool`**  
   Checks if a field exists in a data array, supporting deep paths.

2. **`resolveValueFromData(string $fieldPath, array $data): mixed`**  
   Resolves a value from a data array using deep path notation.

### Modified Methods

| Method | Change |
|--------|--------|
| `hydrateObject()` | Use `fieldExistsInData` + `resolveValueFromData` |
| `hydrateProperty()` | Use `fieldExistsInData` + `resolveValueFromData` |
| `propertyMatchesDataField()` | Use `fieldExistsInData` for both direct and mapping keys |
| `loadSingleProperty()` | Extract value from nested data if deep path is specified |

## Files Changed

### Modified
- `src/Loader/Loader.php` - Deep path support across all hydration methods

### Added  
- `tests/Integration/Features/DeepPathMapping/DeepPathMappingTest.php` - 22 integration tests
- `tests/Integration/Features/DeepPathMapping/Fixtures/AddressDto.php`
- `tests/Integration/Features/DeepPathMapping/Fixtures/AddressWithStreetDto.php`
- `tests/Integration/Features/DeepPathMapping/Fixtures/BookApiDataSource.php`
- `tests/Integration/Features/DeepPathMapping/Fixtures/BookWithFlattenedDetails.php`
- `tests/Integration/Features/DeepPathMapping/Fixtures/BookWithSingleSourceDeepPath.php`
- `tests/Integration/Features/DeepPathMapping/Fixtures/PersonDto.php`
- `tests/Integration/Features/DeepPathMapping/Fixtures/PersonWithDeepAddressDto.php`
- `tests/Integration/Features/DeepPathMapping/Fixtures/PersonWithDeeplyFlattenedAddress.php`
- `tests/Integration/Features/DeepPathMapping/Fixtures/PersonWithFlattenedAddress.php`
- `tests/Integration/Features/DeepPathMapping/Fixtures/StreetDto.php`

## Test Coverage

### DTO Source Tests (ObjectMapper) - 5 tests
- ✅ 2-level nesting (`address.street`)
- ✅ 3-level nesting (`address.street.name`)
- ✅ Null nested objects
- ✅ Partially null paths
- ✅ Mapping to existing objects

### Raw Data Tests (Hydrator) - 9 tests
- ✅ 2-level nesting
- ✅ 3-level nesting
- ✅ Null/missing nested data
- ✅ Edge cases (empty paths, values, arrays)

### MultiPropDataSource Tests - 5 tests
- ✅ 2-level nesting (`book.title`, `book.author.name`)
- ✅ 3-level nesting (`book.publisher.location.city`)
- ✅ Array values (`book.metadata._tags`)
- ✅ Scalar values (`book.metadata._rating`)
- ✅ Multiple properties from same source

### SinglePropDataSource Tests - 3 tests
- ✅ Scalar values (`reviews.average_rating`)
- ✅ Array values (`reviews.items`)
- ✅ Integer values (`reviews.total_count`)

## Test Results

```
PHPUnit 11.5.46
Tests: 22, Assertions: 92
OK (22 tests passed)

All 222 integration tests pass with no regressions.
```

## Breaking Changes

None. This is a bug fix/feature that makes the existing `#[Property(sourceField: 'deep.path')]` attribute work as documented.

## Usage Examples

### MultiPropDataSource with Deep Path

```php
#[DM\DataSourcesStore(items: [
    new DM\MultiPropDataSource(
        id: 'book_api',
        class: BookApiDataSource::class,
        method: 'fetchBookDetails',
        args: ['#isbn']
    ),
])]
class Book
{
    use LoadableTrait;

    private string $isbn;

    // Data source returns: { book: { title: "...", author: { name: "..." } } }
    
    #[DM\Property(sourceField: 'book.title')]
    #[DM\DataSourceRef(id: 'book_api')]
    private ?string $title = null;

    #[DM\Property(sourceField: 'book.author.name')]
    #[DM\DataSourceRef(id: 'book_api')]
    private ?string $authorName = null;
}
```

### SinglePropDataSource with Deep Path

```php
class Book
{
    use LoadableTrait;

    // Data source returns: { reviews: { items: [...], average_rating: 4.5 } }
    
    #[DM\Property(sourceField: 'reviews.average_rating')]
    #[DM\SinglePropDataSource(
        class: BookApiDataSource::class,
        method: 'fetchReviews',
        args: ['#isbn']
    )]
    private ?float $averageRating = null;
}
```
