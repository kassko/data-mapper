# MethodAlias Attribute

The `MethodAlias` attribute defines reusable method references that can be used in data source attributes and expressions.

## Purpose

`MethodAlias` allows you to:
- Define method references at class level for reuse across properties
- Create named functions for use in expression language
- Reference methods on the current object using `##object`
- Simplify complex data source configurations

## Syntax

```php
#[MethodAlias(
    name: string,      // Alias name to reference this method
    class: string,     // Service class, service ID, or '##object' for current object
    method: string,    // Method name to call
    cascade: bool = true  // Whether this attribute cascades to child classes
)]
```

## Usage

### Basic Usage

```php
use Kassko\DataMapper\Attribute\MethodAlias;
use Kassko\DataMapper\Attribute\DataSource;

#[MethodAlias(name: 'fetchPersonData', class: 'person.data_source', method: 'fetchData')]
class Person
{
    private int $id;
    
    #[DataSource(methodAlias: 'fetchPersonData', args: ['#id'])]
    private ?string $email = null;
}
```

### Using `##object` for Current Object Methods

Reference a method on the current object being hydrated:

```php
#[MethodAlias(name: 'checkDataKey', class: '##object', method: 'hasDataKey')]
class Person
{
    private ?string $email = null;
    
    private function hasDataKey(array $data, string $key): bool
    {
        return isset($data[$key]);
    }
}
```

### Using with configCandidates

```php
#[MethodAlias(name: 'personDataKeyExists', class: '##object', method: 'keyExists')]
#[Context(
    ['key' => 'personData', 'class' => 'person.service', 'method' => 'fetchData', 'args' => ['#id']],
)]
#[PropertyConfigStore([
    new PropertyConfig(id: 'personal', name: 'personal-email'),
    new PropertyConfig(id: 'professional', name: 'professional-email'),
])]
class Person
{
    private int $id;
    
    #[Property(
        configCandidates: [
            ['id' => 'personal', 'when' => "expr(personDataKeyExists(context('personData'), 'personal-email'))"],
            ['id' => 'professional', 'when' => "expr(personDataKeyExists(context('personData'), 'professional-email'))"],
        ],
        defaultConfigCandidate: []  // No-op
    )]
    private ?string $email = null;
    
    private function keyExists(array $data, string $key): bool
    {
        return isset($data[$key]);
    }
}
```

## Using methodAlias in DataSource Attributes

The `methodAlias` parameter can be used in:

- `DataSource`
- `SinglePropDataSource`
- `MultiPropDataSource`

When using `methodAlias`, you cannot also specify `class` and `method` - they are mutually exclusive:

```php
// ✅ Correct - uses methodAlias
#[DataSource(methodAlias: 'fetchData', args: ['#id'])]

// ✅ Correct - uses class/method directly
#[DataSource(class: 'MyService', method: 'fetchData', args: ['#id'])]

// ❌ Invalid - cannot combine methodAlias with class/method
#[DataSource(methodAlias: 'fetchData', class: 'MyService', method: 'fetchData')]
```

## Cascading

By default, `MethodAlias` attributes cascade to child classes. Set `cascade: false` to prevent inheritance:

```php
#[MethodAlias(name: 'parentMethod', class: 'service', method: 'method', cascade: false)]
abstract class BaseEntity
{
    // MethodAlias will NOT be available in child classes
}
```

## Error Handling

If a referenced `methodAlias` is not found, a `MethodAliasNotFoundException` is thrown:

```
MethodAlias "fetchData" not found in class "App\Entity\Person" or its parent classes.
Make sure to define the MethodAlias attribute on the class.
```

## Integration with Expression Language

Method aliases are available in expressions:

```php
#[MethodAlias(name: 'checkFeature', class: 'feature.service', method: 'isEnabled')]
class User
{
    #[Property(
        configCandidates: [
            ['id' => 'premium', 'when' => "expr(checkFeature('premium_access'))"],
        ],
        defaultConfigCandidate: 'standard'
    )]
    private ?object $subscription = null;
}
```

## Best Practices

1. **Use descriptive names**: `fetchPersonData` is clearer than `fpd`
2. **Group related aliases**: Define all related method aliases together at class level
3. **Document complex aliases**: Add PHPDoc comments explaining what the alias does
4. **Consider cascade settings**: Disable cascade for class-specific aliases that shouldn't be inherited
