# Context Attribute

The `Context` attribute adds contextual data to the hydration process, enabling conditional behavior based on runtime values.

## Overview

The `Context` attribute supports three context levels:

1. **Application Context**: Shared across all hydration sessions
2. **Hydration Context**: Shared within one hydration session
3. **Object Instance Context**: Specific to one object instance (values may depend on object data)

## Syntax

```php
#[Context(
    ['key' => 'keyName', 'value' => 'staticValue'],                    // Simple key-value
    ['key' => 'dataKey', 'class' => 'service', 'method' => 'fetch'],   // Method result
    ['cascade' => true]  // Optional: last entry can be cascade config
)]
```

Each entry must be an array with:
- `key` (required): The context key name
- Either:
  - `value`: A static value
  - `class` + `method`: A service and method to call to get the value
    - Optional `args`: Arguments to pass to the method

## Usage Examples

### Simple Key-Value Context

```php
use Kassko\DataMapper\Attribute\Context;

class Person
{
    #[Context(
        ['key' => 'environment', 'value' => 'production'],
        ['key' => 'version', 'value' => '2.0']
    )]
    private ?Data $data = null;
}
```

### Capturing Method Results

Fetch data from a service and store it in the context:

```php
#[Context(
    ['key' => 'personRawData', 'class' => 'person.data_source', 'method' => 'fetchData', 'args' => ['#id']],
)]
class Person
{
    private int $id;
    
    // context('personRawData') is now available in expressions
}
```

### Chaining Context Dependencies

Context entries are evaluated in order, allowing later entries to depend on earlier ones:

```php
#[Context(
    ['key' => 'baseData', 'class' => 'base.service', 'method' => 'fetch', 'args' => ['#id']],
    ['key' => 'derivedData', 'class' => 'other.service', 'method' => 'process', 'args' => ["expr(context('baseData'))"]],
)]
class Entity
{
    // Both baseData and derivedData are available
}
```

### Using ##object for Current Object Methods

Reference methods on the current object being hydrated:

```php
#[MethodAlias(name: 'checkKey', class: '##object', method: 'hasKey')]
#[Context(
    ['key' => 'rawData', 'class' => 'data.service', 'method' => 'fetch', 'args' => ['#id']],
)]
class Person
{
    private function hasKey(array $data, string $key): bool
    {
        return isset($data[$key]);
    }
}
```

### Class-Level Context

Context can be applied at class level to set values before any properties are hydrated:

```php
#[Context(
    ['key' => 'entityType', 'value' => 'person'],
    ['key' => 'personData', 'class' => 'person.service', 'method' => 'loadData', 'args' => ['#id']],
)]
class Person
{
    private int $id;
    
    #[Property(
        configCandidates: [
            ['id' => 'premium', 'when' => "expr(context('personData')['type'] === 'premium')"],
        ],
        defaultConfigCandidate: 'standard'
    )]
    private ?Subscription $subscription = null;
}
```

### Property-Level Context

Context can also be applied at property level:

```php
class Company
{
    #[Context(['key' => 'companyId', 'value' => '#id'])]
    private ?Department $department = null;
    
    // When department is hydrated, context('companyId') will be available
}
```

## Accessing Context Values

### In Expressions

```php
#[Property(
    configCandidates: [
        ['id' => 'config1', 'when' => "expr(context('myKey') === 'expectedValue')"],
        ['id' => 'config2', 'when' => "expr(contextKeyExists('otherKey'))"],
    ],
    defaultConfigCandidate: 'default'
)]
private ?object $data = null;
```

### Available Context Functions

- `context('key')` - Returns the value or null if not found (logs warning)
- `contextKeyExists('key')` - Returns true/false

## Application Context via DataMapper

Set context values from your application before hydration:

```php
$dataMapper = new DataMapper($serviceResolver);

// Add individual context value
$dataMapper->addToContext('feature_flag', true);
$dataMapper->addToContext('api_version', 'v2');

// Add multiple context values
$dataMapper->addManyToContext([
    'env' => 'production',
    'debug' => false,
]);

// Check and retrieve context
if ($dataMapper->hasContext('feature_flag')) {
    $value = $dataMapper->getContext('feature_flag');
}
```

## Context Cascading

By default, context attributes cascade to child classes. Control this with the cascade configuration:

```php
// Context will cascade to child classes (default)
#[Context(['key' => 'inherited', 'value' => 'yes'])]

// Context will NOT cascade to child classes
#[Context(['key' => 'notInherited', 'value' => 'no'], ['cascade' => false])]
```

## Context Levels Explained

### Application Context
Set via `DataMapper::addToContext()`. Persists across all hydration sessions.

### Hydration Context
Set at the start of a hydration session. Shared within that session.

### Object Instance Context
Set via `#[Context]` attributes. Values may depend on the specific object being hydrated (e.g., data fetched based on the object's ID). This is useful when context values vary per object instance.

Example:
```php
#[Context(
    ['key' => 'personData', 'class' => 'person.service', 'method' => 'fetch', 'args' => ['#id']],
)]
class Person
{
    private int $id;  // Different for each Person instance
    // personData will be different for each Person based on their id
}
```

## Best Practices

1. **Order matters**: Declare context entries in dependency order
2. **Use meaningful keys**: `personRawData` is clearer than `prd`
3. **Document complex contexts**: Add comments explaining what context values represent
4. **Consider scope**: Use application context for global settings, object context for instance-specific data
5. **Handle missing keys**: Use `contextKeyExists()` to check before accessing optional keys
