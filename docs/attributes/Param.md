# Param Attribute

The `#[Param]` attribute allows you to inject values into constructor, getter, or setter parameters.

## Usage

### In a Constructor

Constructor parameters must **either be optional OR have the `#[Param]` attribute**. Object property references (`#id`, `property('id')`, `##object`) are **forbidden** because the object does not exist yet.

```php
use Kassko\DataMapper\Attribute\Param;

class Person
{
    public function __construct(
        // Optional parameter without #[Param] - uses default value
        ?string $socialSecurityNumber = null,
        // Required parameter with #[Param] - value resolved from context
        #[Param(value: "expr(context('userId'))")]
        string $id = ''
    ) {
        $this->socialSecurityNumber = $socialSecurityNumber;
        $this->id = $id;
    }
}
```

### In a Getter

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

### In a Setter

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

### In an Indexed Adder

The **first two** indexed adder parameters **must not** have `#[Param]` (index and value). Additional parameters (from 3rd position) **can** have `#[Param]`.

```php
use Kassko\DataMapper\Attribute\Param;
use Kassko\DataMapper\Attribute\Setter;

class Person
{
    #[Setter(name: 'addAddress', type: Setter::TYPE_INDEXED_ADDER)]
    private array $addresses = [];

    public function addAddress(
        string $index,  // First parameter: index, no Param
        mixed $address,  // Second parameter: value, no Param
        #[Param(value: "expr(context('address_prefix'))")]
        string $addressPrefix = ''
    ): void {
        $this->addresses[$addressPrefix . $index] = $address;
    }
}
```

## Supported Values

### Static Value

```php
#[Param(value: "fixed value")]
```

### context() Expression

Retrieves a value from context (defined via `ContextRegistry` or `DataMapper::addToContext()`).

```php
#[Param(value: "expr(context('userId'))")]
```

### service() Expression

Injects a service from the ServiceResolver.

```php
#[Param(value: "expr(service('myService'))")]
```

### source() Expression

Executes a data source and returns its result.

```php
#[Param(value: "expr(source('dataSourceId'))")]
```

### property() Expression (Getters/Setters Only)

References an object property (forbidden in constructors).

```php
#[Param(value: "expr(property('name'))")]
// or short syntax
#[Param(value: "#name")]
```

### Current Object Reference (Getters/Setters Only)

```php
#[Param(value: "##object")]
```

## Validation Rules

| Context | Rules |
|---------|-------|
| Constructor | Parameters must be **optional OR have `#[Param]`**. `#id`, `property()`, `##object` are forbidden. |
| Getter | Parameters with `#[Param]` are injected; parameters without Param must have a default value. |
| Setter | First parameter **without** `#[Param]`; subsequent parameters **with** `#[Param]`. |
| Indexed Adder | First two parameters **without** `#[Param]`; subsequent parameters **with** `#[Param]`. |

## Complete Example

```php
use Kassko\DataMapper\Attribute\DataSourceRef;
use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\Attribute\MultiPropDataSource;
use Kassko\DataMapper\Attribute\Param;
use Kassko\DataMapper\Attribute\Setter;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

#[DataSourcesStore([
    new MultiPropDataSource(id: 'userApi', class: 'UserApiService', method: 'getUser'),
])]
class User
{
    use LoadableTrait;
    
    private string $id;
    
    // Constructor with optional param and Param-annotated param
    public function __construct(
        ?string $externalId = null,  // Optional, uses default
        #[Param(value: "expr(context('requestedUserId'))")]
        string $id = ''
    ) {
        $this->id = $id;
    }
    
    #[DataSourceRef(id: 'userApi')]
    private ?string $name = null;
    
    #[Setter(name: 'addAddress', type: Setter::TYPE_INDEXED_ADDER)]
    private array $addresses = [];
    
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
    
    public function addAddress(
        mixed $index,
        mixed $address,
        #[Param(value: "expr(context('address_prefix'))")]
        string $prefix = ''
    ): void {
        $this->addresses[$prefix . $index] = $address;
    }
}
```
