# MultiPropDataSource

Defines a data source that hydrates multiple properties from an associative array.

## Usage

```php
use Kassko\DataMapper\Attribute\MultiPropDataSource;
use Kassko\DataMapper\Attribute\DataSourceRef;
use Kassko\DataMapper\Attribute\Property;

#[MultiPropDataSource(
    id: 'personData',
    class: PersonRepository::class,
    method: 'findById',
    args: ['#id'],
    loadingScope: MultiPropDataSource::SCOPE_ONLY_PROPS,
    loadingScopeProps: ['firstName', 'lastName']
)]
class Person
{
    private int $id;
    
    #[DataSourceRef(id: 'personData')]
    #[Property(name: 'first_name')]
    private ?string $firstName = null;
    
    #[DataSourceRef(id: 'personData')]
    #[Property(name: 'last_name')]
    private ?string $lastName = null;
    
    #[DataSourceRef(id: 'personData')]
    private ?string $email = null;  // Excluded by loadingScopeProps
}
```

## Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `id` | `?string` | No | `null` | Unique identifier for referencing via DataSourceRef |
| `class` | `?string` | No | `null` | Service class to call |
| `method` | `string` | No | `''` | Method to call on the service |
| `args` | `array` | No | `[]` | Arguments to pass to the method |
| `loadingScope` | `string` | No | `'all'` | How to filter properties |
| `loadingScopeKeys` | `array` | No | `[]` | Raw data keys to include/exclude |
| `loadingScopeProps` | `array` | No | `[]` | Property names to include/exclude |

## Loading Scope

| Value | Description |
|-------|-------------|
| `all` | Hydrate all eligible properties |
| `only_keys` | Only hydrate properties matching keys in `loadingScopeKeys` |
| `except_keys` | Hydrate all except properties matching keys in `loadingScopeKeys` |
| `only_props` | Only hydrate properties listed in `loadingScopeProps` |
| `except_props` | Hydrate all except properties listed in `loadingScopeProps` |

## Class-Level Attribute

Unlike `SinglePropDataSource`/`DataSource`, this attribute is placed on the **class** because it affects multiple properties.

```php
#[MultiPropDataSource(...)]  // ← On the class
class Person
{
    #[DataSourceRef(id: '...')]  // ← Properties reference it
    private ?string $name = null;
}
```

## See Also

- [SinglePropDataSource](SinglePropDataSource.md)
- [DataSource](DataSource.md)
- [DataSourceRef](DataSourceRef.md)
