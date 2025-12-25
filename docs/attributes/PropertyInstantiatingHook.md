# PropertyInstantiatingHook Attribute

The `PropertyInstantiatingHook` attribute allows you to execute custom logic immediately after an object is instantiated during hydration, before any properties are set.

## Scope

- **Target:** Classes only
- **Repeatable:** Yes (can apply multiple hooks)

## Parameters

- `after_instantiating` (string): Method name to call after object instantiation
- `class` (string|null): Optional external class/service to call the method on
- `args` (array): Arguments to pass to the hook method (supports `##object`, `expr()`)

## Usage

```php
use Kassko\DataMapper\Attribute\PropertyInstantiatingHook;

#[PropertyInstantiatingHook(
    after_instantiating: 'initialize',
    args: ['##object']
)]
class Person
{
    private bool $initialized = false;
    private ?string $firstName = null;
    private ?string $lastName = null;

    public function initialize(self $obj): void
    {
        $this->initialized = true;
        // Perform any initialization logic
    }

    public function isInitialized(): bool
    {
        return $this->initialized;
    }
}
```

## Hook Execution Timing

The `after_instantiating` hook is executed:
1. After the object is instantiated with `new ClassName()`
2. Before any properties are hydrated
3. Before `before_hydrate_object` hooks (if any)

## Special Arguments

- `##object` - The current object being hydrated
- `expr(...)` - Expression evaluation with access to raw data

## External Service Example

```php
#[PropertyInstantiatingHook(
    after_instantiating: 'trackCreation',
    class: AuditService::class,
    args: ['##object', "expr(rawDataItem('id'))"]
)]
class AuditedEntity
{
    private ?int $id = null;
}
```

## Use Cases

- Initialize default values or computed properties
- Set up internal state
- Perform validation or security checks
- Register the object with a tracker or registry
- Set up event listeners

## Notes

- Multiple hooks can be applied and will execute in declaration order
- The hook is called for both top-level objects and nested objects during recursive hydration
- Renamed from the legacy `Hook` attribute with `name: 'after_create_object'`
