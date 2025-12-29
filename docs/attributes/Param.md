# Param Attribute

The `#[Param]` attribute allows you to inject values into constructor, getter, or setter parameters.

## Usage

### In a constructor

All constructor parameters **must** have the `#[Param]` attribute. Object property references (`#id`, `property('id')`, `##object`) are **forbidden** because the object does not exist yet.

```php
use Kassko\DataMapper\Attribute\Param;

class Person
{
    private string $id;
    
    public function __construct(
        #[Param(value: "expr(context('userId'))")]
        string $id
    ) {
        $this->id = $id;
    }
}
```

### In a getter

Parameters with `#[Param]` are automatically injected. Property references are allowed.

```php
use Kassko\DataMapper\Attribute\Param;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

class Person
{
    use LoadableTrait;
    
    private ?string $name = null;
    
    public function getName(
        #[Param(value: "expr(service('nameFormatter'))")]
        $formatter = null
    ): ?string {
        $this->loadProperty('name');
        return $formatter ? $formatter->format($this->name) : $this->name;
    }
}
```

### In a setter

The **first** setter parameter **must not** have `#[Param]` (it's the value to assign). Additional parameters **must** have `#[Param]`.

```php
use Kassko\DataMapper\Attribute\Param;

class Person
{
    private ?string $email = null;
    
    public function setEmail(
        $email,  // First parameter: normal value, no Param
        #[Param(value: "expr(context('enforcedEmail'))")]
        $enforcedEmail = null
    ): void {
        $this->email = $enforcedEmail ?: $email;
    }
}
```

## Supported values

### Static value

```php
#[Param(value: "fixed value")]
```

### context() expression

Retrieves a value from context (defined via `ContextRegistry` or `DataMapper::addToContext()`).

```php
#[Param(value: "expr(context('userId'))")]
```

### service() expression

Injects a service from the ServiceResolver.

```php
#[Param(value: "expr(service('myService'))")]
```

### source() expression

Executes a data source and returns its result.

```php
#[Param(value: "expr(source('dataSourceId'))")]
```

### property() expression (getters/setters only)

References an object property (forbidden in constructors).

```php
#[Param(value: "expr(property('name'))")]
// or short syntax
#[Param(value: "#name")]
```

### Current object reference (getters/setters only)

```php
#[Param(value: "##object")]
```

## Validation rules

| Context | Rules |
|---------|-------|
| Constructor | All parameters **must** have `#[Param]`. `#id`, `property()`, `##object` are forbidden. |
| Getter | Parameters with `#[Param]` are injected; parameters without Param must have a default value. |
| Setter | First parameter **without** `#[Param]`; subsequent parameters **with** `#[Param]`. |

## Complete example

```php
use Kassko\DataMapper\Attribute\DataSourceRef;
use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\Attribute\MultiPropDataSource;
use Kassko\DataMapper\Attribute\Param;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

#[DataSourcesStore([
    new MultiPropDataSource(id: 'userApi', class: 'UserApiService', method: 'getUser'),
])]
class User
{
    use LoadableTrait;
    
    private string $id;
    
    public function __construct(
        #[Param(value: "expr(context('requestedUserId'))")]
        string $id
    ) {
        $this->id = $id;
    }
    
    #[DataSourceRef(id: 'userApi')]
    private ?string $name = null;
    
    public function getName(
        #[Param(value: "expr(service('nameFormatter'))")]
        $formatter = null
    ): ?string {
        $this->loadProperty('name');
        return $formatter?->format($this->name) ?? $this->name;
    }
    
    public function setName(
        $name,
        #[Param(value: "expr(context('namePrefix'))")]
        $prefix = ''
    ): void {
        $this->name = $prefix . $name;
    }
}
```
