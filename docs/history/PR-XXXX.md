# PR-XXXX: Major Refactoring - Expression Language, Service Providers, Cache Support, and Loader Rename

## Summary

This PR contains several major improvements and breaking changes to the data-mapper library:

1. **Expression Language Refactoring** - Updated syntax for clarity
2. **Service Providers** - Added support for factory services, static factories, and callables
3. **PSR-16 Cache Support** - Added caching interface for DataSource results
4. **Attribute Renaming** - Renamed `exception` to `exceptionOnNoValidDataSource` in DataSourceRef
5. **LazyLoader Renamed to Loader** - Better naming since we support both lazy and eager loading
6. **Documentation Corrections** - Fixed MultiPropDataSource usage and exception class names

## Breaking Changes

### Expression Language

#### Simple Expressions
| Old Syntax | New Syntax | Description |
|------------|------------|-------------|
| `##this` | `##object` | Current object |
| `#parent` | `#parentObject` | Parent object |
| `##parent` | `##parentObject` | Parent object (alias) |

#### Advanced Expressions (`expr()`)
| Old Function | New Function | Description |
|--------------|--------------|-------------|
| `_this()` | `object()` | Current object |
| `_self()` | **Removed** | Use `object()` instead |
| `thisParent()` | `parentObject()` | Parent object |

### DataSourceRef Attribute

The `exception` parameter has been renamed to `exceptionOnNoValidDataSource` for clarity:

```php
// Before
#[DataSourceRef(
    chain: ['sourceA', 'sourceB'],
    exception: NoValidDataSourceException::class
)]

// After
#[DataSourceRef(
    chain: ['sourceA', 'sourceB'],
    exceptionOnNoValidDataSource: NoValidDataSourceException::class
)]
```

### LazyLoader → Loader

All `LazyLoader` classes have been renamed to `Loader`:

| Old Class | New Class |
|-----------|-----------|
| `Kassko\DataMapper\LazyLoader\LazyLoader` | `Kassko\DataMapper\Loader\Loader` |
| `Kassko\DataMapper\LazyLoader\LazyLoaderInterface` | `Kassko\DataMapper\Loader\LoaderInterface` |
| `Kassko\DataMapper\Registry\LazyLoaderRegistry` | `Kassko\DataMapper\Registry\LoaderRegistry` |

## New Features

### Service Providers

The `DataMapperBuilder` now supports multiple ways to resolve services:

```php
use Kassko\DataMapper\DataMapperBuilder;

$dataMapper = (new DataMapperBuilder())
    ->setContainer($container)           // PSR-11 container
    ->addLocator($familyLocator)          // Service locators
    ->addLocator($vehicleLocator)
    ->addFactoryService($factoryService, 'createInstance')  // Factory service
    ->addStaticFactory($factoryClass, 'createInstance')     // Static factory
    ->addCallable($callable)              // Callable factory
    ->build();
```

#### Supported Callable Types
- `['MyClass', 'myMethod']` - Array callable
- `[$this, 'myMethod']` - Instance method
- `'MyClass::myMethod'` - Static method string
- `Closure::fromCallable(...)` - Closure
- `fn($key) => ...` - Arrow function

#### Service Resolution Order
Services are resolved in the following order (from most likely to least likely):
1. Container (PSR-11) - broadest scope
2. Service locators - domain-specific locators
3. Factory services - instance-based factories
4. Static factories - class-based factories
5. Callables - custom factory functions
6. Direct instantiation - fallback

### PSR-16 Cache Support

The library now supports PSR-16 caching for DataSource results:

```php
use Kassko\DataMapper\DataMapperBuilder;
use Kassko\DataMapper\DataMapper;

// Via builder
$dataMapper = (new DataMapperBuilder())
    ->setCache($cache)  // PSR-16 CacheInterface
    ->build();

// Direct instantiation
$dataMapper = new DataMapper($serviceResolver, $cache);
```

The `SourceFunctionProvider` now uses the PSR-16 cache interface instead of an internal array cache:

```php
use Kassko\DataMapper\Expression\SourceFunctionProvider;

$provider = new SourceFunctionProvider($executor, $cache);
$provider->getSourceResult('sourceId');  // Results are cached
$provider->clearCache('sourceId');       // Clear specific source
$provider->clearCache();                 // Clear all cached results
```

## Documentation Corrections

### MultiPropDataSource Usage

`MultiPropDataSource` must now be declared inside `DataSourcesStore`, not directly on classes:

```php
// ✅ Correct
#[DataSourcesStore([
    new MultiPropDataSource(
        id: 'personData',
        class: PersonRepository::class,
        method: 'findById',
        args: ['#id']
    ),
])]
class Person { }

// ❌ Incorrect - No longer supported
#[MultiPropDataSource(...)]
class Person { }
```

### Exception Class Names

Updated documentation to use `NoValidDataSourceException` instead of `UnsuitableSourceException`.

## Migration Guide

### 1. Update Expression Language

Search and replace in your codebase:
- `##this` → `##object`
- `#parent` or `##parent` → `#parentObject` or `##parentObject`
- `_this()` or `_self()` → `object()`
- `thisParent()` → `parentObject()`

### 2. Update DataSourceRef

Replace `exception:` with `exceptionOnNoValidDataSource:` in all `DataSourceRef` attributes.

### 3. Update LazyLoader References

Replace all `LazyLoader` imports and usages:
- `use Kassko\DataMapper\LazyLoader\LazyLoader` → `use Kassko\DataMapper\Loader\Loader`
- `use Kassko\DataMapper\LazyLoader\LazyLoaderInterface` → `use Kassko\DataMapper\Loader\LoaderInterface`
- `use Kassko\DataMapper\Registry\LazyLoaderRegistry` → `use Kassko\DataMapper\Registry\LoaderRegistry`
- `new LazyLoader(...)` → `new Loader(...)`
- `LazyLoaderRegistry::get()` → `LoaderRegistry::get()`

### 4. Update MultiPropDataSource Declarations

Wrap any `MultiPropDataSource` attributes in `DataSourcesStore`:

```php
// Before
#[MultiPropDataSource(id: 'source1', ...)]
#[MultiPropDataSource(id: 'source2', ...)]
class MyClass { }

// After
#[DataSourcesStore([
    new MultiPropDataSource(id: 'source1', ...),
    new MultiPropDataSource(id: 'source2', ...),
])]
class MyClass { }
```

## Files Changed

### Source Files
- `src/Expression/ExpressionParser.php` - Updated expression language
- `src/Expression/SourceFunctionProvider.php` - Added PSR-16 cache support
- `src/ServiceResolver.php` - Added factory service support
- `src/DataMapperBuilder.php` - Added new builder methods
- `src/DataMapper.php` - Added cache parameter
- `src/Attribute/DataSourceRef.php` - Renamed exception field
- `src/Attribute/Hook.php` - Updated comment
- `src/Loader/Loader.php` - Renamed from LazyLoader
- `src/Loader/LoaderInterface.php` - Renamed from LazyLoaderInterface
- `src/Registry/LoaderRegistry.php` - Renamed from LazyLoaderRegistry
- `src/ObjectExtension/LoadableTrait.php` - Updated to use LoaderRegistry

### Deleted Files
- `src/LazyLoader/LazyLoader.php`
- `src/LazyLoader/LazyLoaderInterface.php`
- `src/Registry/LazyLoaderRegistry.php`

### Documentation
- `README.md` - Updated expression language documentation
- `EXAMPLE.md` - Updated examples
- `docs/attributes/DataSourceRef.md` - Updated with new field name and DataSourcesStore usage
- `docs/attributes/SinglePropDataSource.md` - Fixed MultiPropDataSource usage
- `docs/attributes/Needs.md` - Fixed MultiPropDataSource usage

### Tests
- All test files updated to use new class names and syntax
- Test files renamed: `LazyLoaderTest.php` → `LoaderTest.php`, etc.

## Testing

All 165 tests pass (2 skipped).

```bash
composer install
vendor/bin/phpunit
```
