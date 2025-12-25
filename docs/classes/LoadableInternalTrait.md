# LoadableInternalTrait

**⚠️ INTERNAL USE ONLY - DO NOT USE IN PRODUCTION CODE**

## Purpose

This trait is for internal library testing and examples only. It extends `LoadableTrait` with additional methods needed for testing eager loading functionality.

## When to Use

- **Testing**: Use this trait in test fixtures when you need to manually trigger eager loading
- **Internal Examples**: Use in library examples that demonstrate eager loading

## When NOT to Use

- **Production Code**: Never use this trait in your application's domain objects
- **User Applications**: Always use `LoadableTrait` instead

## What It Adds

The trait adds one public method that is not available in the standard `LoadableTrait`:

### `loadEagerProperties(): void`

Manually triggers loading of all properties marked with `Loading::TYPE_EAGER`.

**Note**: In normal usage, eager properties are loaded automatically by the DataMapper. This method exists only for testing purposes.

## Example (Test Fixtures Only)

```php
use Kassko\DataMapper\ObjectExtension\LoadableInternalTrait;

class TestEntity
{
    use LoadableInternalTrait;
    
    #[Loading(type: Loading::TYPE_EAGER)]
    private ?string $eagerProperty = null;
    
    // ... rest of the class
}

// In tests only:
$entity = new TestEntity();
$entity->loadEagerProperties(); // Manually trigger eager loading for testing
```

## For Production Code

Use `LoadableTrait` instead:

```php
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

class MyEntity
{
    use LoadableTrait;
    
    // Eager properties are loaded automatically
    // No need to call loadEagerProperties()
}
```

## See Also

- [LoadableTrait](../classes/LoadableTrait.md) - The public trait for production use
- [Loading Attribute](../attributes/Loading.md) - How to configure eager loading
