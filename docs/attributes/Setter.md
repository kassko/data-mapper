# Setter

Specifies a custom setter method for a property. Supports different setter types including standard setters, adders, and indexed adders.

## Basic Usage

```php
use Kassko\DataMapper\Attribute\Setter;

class Entity
{
    #[Setter(name: 'setName')]
    private ?string $name = null;
    
    public function setName(?string $value): void
    {
        $this->name = strtoupper($value);
    }
}
```

## Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `name` | `string` | Yes | - | Name of the setter method |
| `type` | `string` | No | `setter` | Type of setter: `setter`, `adder`, or `indexed_adder` |
| `cascade` | `bool` | No | `true` | Whether this attribute cascades to child classes |
| `enabled` | `bool` | No | `true` | Whether this attribute is active |

## Setter Types

### Standard Setter (`TYPE_SETTER`)

The default behavior. Calls the method with the property value.

```php
use Kassko\DataMapper\Attribute\Setter;

class Person
{
    #[Setter(name: 'setName', type: Setter::TYPE_SETTER)]
    private ?string $name = null;
    
    public function setName(?string $value): void
    {
        $this->name = strtoupper($value);
    }
}
```

### Adder (`TYPE_ADDER`)

For collection properties. Calls the method once for each item in the array.

```php
use Kassko\DataMapper\Attribute\Setter;

class Person
{
    #[Setter(name: 'addEmail', type: Setter::TYPE_ADDER)]
    private array $emails = [];
    
    public function addEmail(string $email): void
    {
        $this->emails[] = strtolower($email);
    }
}

// With data: ['emails' => ['A@TEST.COM', 'B@TEST.COM']]
// Results in: addEmail('A@TEST.COM'), addEmail('B@TEST.COM')
```

### Indexed Adder (`TYPE_INDEXED_ADDER`)

For associative array properties. Calls the method with both the key and value.

```php
use Kassko\DataMapper\Attribute\Setter;

class Person
{
    #[Setter(name: 'addAddress', type: Setter::TYPE_INDEXED_ADDER)]
    private array $addresses = [];
    
    public function addAddress(mixed $key, mixed $address): void
    {
        $this->addresses[$key] = $address;
    }
}

// With data: ['addresses' => ['home' => '123 Main St', 'work' => '456 Office Blvd']]
// Results in: addAddress('home', '123 Main St'), addAddress('work', '456 Office Blvd')
```

### Indexed Adder with Param

You can use `#[Param]` attributes on additional parameters (from the 3rd position) in indexed adders:

```php
use Kassko\DataMapper\Attribute\Param;
use Kassko\DataMapper\Attribute\Setter;

class Person
{
    #[Setter(name: 'addAddress', type: Setter::TYPE_INDEXED_ADDER)]
    private array $addresses = [];
    
    public function addAddress(
        mixed $index,
        mixed $address,
        #[Param(value: "expr(context('address_prefix'))")]
        string $addressPrefix = ''
    ): void {
        $this->addresses[$addressPrefix . $index] = $address;
    }
}

// With context('address_prefix') = 'addr_'
// and data: ['addresses' => ['home' => '123 Main St']]
// Results in: $this->addresses['addr_home'] = '123 Main St'
```

## Auto-Detection (Without Attribute)

When no `#[Setter]` attribute is present, DataMapper uses conventional patterns:

1. **List arrays**: Looks for `add{PropertyName}Item()` method
2. **All arrays**: Falls back to `set{PropertyName}()` method
3. **Direct assignment**: Falls back to reflection if no setter found

```php
class Person
{
    // For ['emails' => ['a@test.com', 'b@test.com']]
    // Will call addEmailsItem() for each item
    private array $emails = [];
    
    public function addEmailsItem(string $email): void
    {
        $this->emails[] = $email;
    }
}
```

## See Also

- [Getter](Getter.md)
- [Param](Param.md)
