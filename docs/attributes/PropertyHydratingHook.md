# PropertyHydratingHook Attribute

The `PropertyHydratingHook` attribute allows you to execute custom logic before and/or after an object is hydrated with data.

## Scope

- **Target:** Classes only
- **Repeatable:** Yes (can apply multiple hooks)

## Parameters

- `before_hydrate_object` (string): Method name to call before hydration starts
- `after_hydrate_object` (string): Method name to call after hydration completes
- `class` (string|null): Optional external class/service to call the method on
- `args` (array): Arguments to pass to the hook method (supports `##object`, `expr()`)

## Usage

```php
use Kassko\DataMapper\Attribute\PropertyHydratingHook;

#[PropertyHydratingHook(
    before_hydrate_object: 'prepareForHydration',
    after_hydrate_object: 'finalizeHydration'
)]
class Person
{
    private ?string $firstName = null;
    private ?string $lastName = null;
    private ?string $fullName = null;

    public function prepareForHydration(array $rawData): void
    {
        // Called before any properties are set
        // Access raw data for preprocessing
        if (isset($rawData['legacy_format'])) {
            // Handle legacy data format
        }
    }

    public function finalizeHydration(?object $object, array $rawData): void
    {
        // Called after all properties are set
        // Compute derived values
        $this->fullName = trim(($this->firstName ?? '') . ' ' . ($this->lastName ?? ''));
    }
}
```

## Hook Signatures

### before_hydrate_object
```php
public function methodName(array $rawData): void
```
- `$rawData` - The raw data array being used for hydration

### after_hydrate_object
```php
public function methodName(?object $object, array $rawData): void
```
- `$object` - The object being hydrated (can be null if custom hydrator returned null)
- `$rawData` - The raw data array that was used for hydration

## Hook Execution Order

1. Object is instantiated
2. `after_instantiating` hooks (if any)
3. **`before_hydrate_object` hooks** ← Executed here
4. Properties are hydrated (with `before_set_property` and `after_set_property` hooks)
5. **`after_hydrate_object` hooks** ← Executed here

## Use Cases

### Before Hydration
- Preprocess or transform raw data
- Set up internal state
- Validate incoming data
- Handle data format migrations

### After Hydration
- Compute derived/calculated fields
- Validate object state
- Trigger side effects
- Update caches or indexes
- Send notifications

## External Service Example

```php
#[PropertyHydratingHook(
    before_hydrate_object: 'validateData',
    class: ValidationService::class
)]
#[PropertyHydratingHook(
    after_hydrate_object: 'auditChange',
    class: AuditService::class
)]
class ImportantEntity
{
    // Properties...
}
```

## Special Arguments

While the hook signatures are fixed for `before_hydrate_object` and `after_hydrate_object`, you can use the `args` parameter to pass additional context when needed.

## Notes

- Both `before_hydrate_object` and `after_hydrate_object` can be specified in the same attribute
- If only one is needed, leave the other as an empty string
- Multiple hooks can be applied and will execute in declaration order
- These hooks apply to the entire object hydration process, not individual properties
- The `after_hydrate_object` hook is called before individual property `before_set_property` hooks
