# Context

Adds key-value pairs to the hydration context, enabling conditional behavior based on parent object configuration.

## Overview

The `Context` attribute allows you to pass contextual information down through the object hierarchy during hydration. Context values accumulate as hydration descends into nested objects, and later values for the same key override earlier ones (last writer wins).

## Usage

### Basic Usage with Named Arguments

```php
use Kassko\DataMapper\Attribute\Context;

class CompanyA
{
    #[Context(
        key1: 'value1',
        key2: 'value2',
        key3: 'value3'
    )]
    private Chief $chief;
    
    // Adds 3 variables to the context when chief is hydrated
}
```

### Context Accumulation

Context values accumulate as you descend into nested objects:

```php
class CompanyB
{
    #[Context(key3: 'value3')]
    private Chief $chief;
    // Adds 1 variable to the context
}

class Chief
{
    #[Context(
        key2: 'new_value2',  // Overrides any previous key2 value
        key4: 'value4'       // Adds new key
    )]
    private $prop;
}
```

### Using Context in Expressions

Access context values in expressions using `context()` and `contextKeyExists()`:

```php
class Chief
{
    #[Context(key2: 'new_value2', key4: 'value4')]
    #[Property(
        args: "expr(context('key1') === 'foo' ? property('propA') : property('propB'))"
    )]
    private $prop;
}
```

### Using Context with PropertyCandidates

```php
class Chief
{
    #[Context(key2: 'new_value2', key4: 'value4')]
    #[PropertyCandidates([
        new PropertyCandidate(
            discriminator: "expr(context('key1') === 'value1')",
            property: new Property(class: TypeA::class)
        ),
        new PropertyCandidate(
            discriminator: "expr(contextKeyExists('key3'))",
            property: new Property(class: TypeB::class)
        )
    ])]
    private $prop;
}
```

## Application Context

You can also set context values from your application before hydration:

```php
$dataMapper = new DataMapper($serviceResolver);

// Add application-level context
$dataMapper->addToContext('new_profile_api_feature', $featureFlag->isEnabled());
$dataMapper->addToContext('current_user_role', 'admin');

// Or add multiple at once
$dataMapper->addManyToContext([
    'env' => 'production',
    'debug' => false,
]);
```

Application context values are available during hydration and can be overridden by `#[Context]` attributes.

## Expression Functions

| Function | Description |
|----------|-------------|
| `context('key')` | Get context value (returns null if not found, logs warning) |
| `contextKeyExists('key')` | Check if context key exists (returns boolean) |

## Context Behavior

1. **Accumulation**: Context values accumulate as hydration descends into nested objects
2. **Override**: Later values for the same key override earlier ones (last writer wins)
3. **Timing**: Context values are set AFTER the property is hydrated
4. **Precedence**: Hydration context (`#[Context]`) takes precedence over application context

## See Also

- [Property](Property.md)
- [PropertyCandidates](PropertyCandidates.md)
