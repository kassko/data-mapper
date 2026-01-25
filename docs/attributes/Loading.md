# Loading

Controls lazy loading behavior and depth limits for property hydration.

## Usage

```php
use Kassko\DataMapper\Attribute\Loading;
use Kassko\DataMapper\Attribute\Property;
use Kassko\DataMapper\Attribute\SinglePropDataSource;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

class Company
{
    use LoadableTrait;
    
    private ?string $name = null;
    
    // Lazy loading - loads department when accessed
    #[SinglePropDataSource(class: DepartmentService::class, method: 'getDepartment')]
    #[Property(class: Department::class)]
    #[Loading(type: Loading::TYPE_LAZY, depth: 2)]
    private ?Department $department = null;
    
    // Eager loading - loads immediately during hydration
    #[SinglePropDataSource(class: SettingsService::class, method: 'getSettings')]
    #[Property(class: Settings::class)]
    #[Loading(type: Loading::TYPE_EAGER)]
    private ?Settings $settings = null;
    
    public function getDepartment(): ?Department
    {
        $this->loadProperty('department');
        return $this->department;
    }
}
```

## Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `type` | `string` | No | `TYPE_LAZY` | Loading type: `TYPE_LAZY` or `TYPE_EAGER` |
| `depth` | `?int` | No | `null` | Maximum depth for nested object hydration |

## Loading Types

### Lazy Loading (default)
Property is loaded when first accessed via getter that calls `$this->loadProperty()`.

### Eager Loading
Property is loaded immediately when the parent object is hydrated.

## Depth Control

The `depth` parameter controls how deep nested object hydration should go:

| Depth Value | Behavior |
|-------------|----------|
| `null` | No limit - hydrate all nested levels (default) |
| `0` | Hydrate only the root object's scalar properties |
| `1` | Hydrate root object + direct child objects |
| `2` | Hydrate root + children + grandchildren |
| `N` | Hydrate N levels deep |

### Depth Example

Given this structure:
- Organization → Department → Team → Employee

```php
class Organization
{
    use LoadableTrait;
    
    // depth=0: Only Department scalars (name, code)
    // Team and Employee are NOT hydrated
    #[SinglePropDataSource(class: DeptService::class, method: 'get')]
    #[Property(class: Department::class)]
    #[Loading(depth: 0)]
    private ?Department $department = null;
    
    // depth=1: Department + Team (but not Employee)
    #[SinglePropDataSource(class: DeptService::class, method: 'get')]
    #[Property(class: Department::class)]
    #[Loading(depth: 1)]
    private ?Department $departmentWithTeam = null;
    
    // depth=2: Department + Team + Employee
    #[SinglePropDataSource(class: DeptService::class, method: 'get')]
    #[Property(class: Department::class)]
    #[Loading(depth: 2)]
    private ?Department $fullDepartment = null;
}
```

## Collaboration with Property::expand/noExpand

When using `depth` with `expand` or `noExpand` on the Property attribute:

1. **Depth is evaluated first** - if exceeded, the nested object is not hydrated
2. **expand/noExpand filters** which properties are hydrated within the depth limit

```php
// depth=1 allows Team hydration, expand filters which Team properties
#[SinglePropDataSource(class: DeptService::class, method: 'get')]
#[Property(class: Department::class, expand: 'name,code,mainTeam')]
#[Loading(depth: 1)]
private ?Department $department = null;

// depth=0 prevents ALL nested objects, even if expand includes them
#[SinglePropDataSource(class: DeptService::class, method: 'get')]
#[Property(class: Department::class, expand: 'name,mainTeam')]
#[Loading(depth: 0)]
private ?Department $shallowDepartment = null;
// mainTeam will NOT be hydrated because depth=0 takes precedence
```

## Related Attributes

- [Property](Property.md) - Define class mapping and expand/noExpand filters
- [SinglePropDataSource](SinglePropDataSource.md) - Define data source for property
- [DataSourceRef](DataSourceRef.md) - Reference a data source from store
