# PropertySettingHook Attribute

The `PropertySettingHook` attribute allows you to execute custom logic before and/or after a property value is set during hydration.

## Scope

- **Target:** Properties only
- **Repeatable:** Yes (can apply multiple hooks)

## Parameters

- `before_set_property` (string): Method name to call before setting the property value
- `after_set_property` (string): Method name to call after setting the property value  
- `class` (string|null): Optional external class/service to call the method on
- `args` (array): Arguments to pass to the hook method (supports `##object`, `#property`, `expr()`)

## Usage

```php
use Kassko\DataMapper\Attribute\PropertySettingHook;

class Person
{
    #[PropertySettingHook(
        before_set_property: 'validateName',
        args: ["expr(rawDataItem('first_name'))"]
    )]
    #[PropertySettingHook(
        after_set_property: 'onNameSet',
        args: ['##object', '#firstName']
    )]
    private ?string $firstName = null;

    public function validateName(?string $name): void
    {
        if ($name && strlen($name) < 2) {
            throw new \InvalidArgumentException('Name too short');
        }
    }

    public function onNameSet(self $obj, ?string $firstName): void
    {
        // Custom logic after property is set
        $this->fullNameDirty = true;
    }
}
```

## Hook Execution Order

1. `before_set_property` hooks are executed
2. Property value is set (via setter, adder, or direct assignment)
3. `after_set_property` hooks are executed

## Special Arguments

- `##object` - The current object being hydrated
- `#propertyName` - Value of another property (triggers loading if needed)
- `expr(...)` - Expression evaluation with access to raw data

## External Service Example

```php
#[PropertySettingHook(
    before_set_property: 'validate',
    class: ValidationService::class,
    args: ['##object', "expr(rawDataItem('email'))"]
)]
private ?string $email = null;
```

## Notes

- Both `before_set_property` and `after_set_property` can be specified in the same attribute
- If only one is needed, leave the other as an empty string
- Hooks are executed in the order they are declared
- Hook methods can be on the object itself or on an external service
