# Expression Language

The DataMapper Expression Language provides a powerful way to reference data dynamically during object hydration. This document covers all available expression patterns, from simple property references to complex context-based evaluations.

## Overview

Expressions in DataMapper allow you to:
- Reference property values from the current object
- Access context values shared across hydration
- Call data sources dynamically
- Access environment variables
- Perform conditional logic in `configCandidates` and `candidates`

## Basic Property References

### Simple Property Reference: `#propertyName`

The most basic expression is a property reference. Use `#` followed by the property name to reference another property's value.

```php
use Kassko\DataMapper\Attribute\DataSource;

class Person
{
    private int $id;
    
    #[DataSource(class: EmailService::class, method: 'getEmail', args: ['#id'])]
    private ?string $email = null;
}
```

The `#id` will be resolved to the current value of the `$id` property when the data source method is called.

### Object Reference: `##object`

Reference the current object being hydrated:

```php
#[PropertyInstantiatingHook(after_instantiating: 'onCreated', args: ['##object'])]
class Entity
{
    public function onCreated(self $entity): void
    {
        // Called with the entity itself
    }
}
```

### Parent Object Reference: `#parentObject` or `##parentObject`

Reference the parent object when hydrating nested objects:

```php
class Child
{
    #[DataSource(class: DataService::class, method: 'getData', args: ['#parentObject'])]
    private ?string $data = null;
}
```

### Direct Property Access: `!#propertyName`

Bypass the getter method and access the property value directly:

```php
#[DataSource(class: Service::class, method: 'process', args: ['!#internalValue'])]
private ?string $result = null;
```

## Expression Functions: `expr(...)`

For more complex scenarios, wrap your expression in `expr()`:

### Context Access: `context('key')` and `contextKeyExists('key')`

Access values from the hydration context:

```php
#[DataSource(class: Service::class, method: 'getData', args: ["expr(context('user_id'))"])]
private ?string $data = null;
```

Check if a context key exists:

```php
#[Property(
    configCandidates: [
        ['id' => 'premium', 'when' => "expr(contextKeyExists('premium_enabled'))"],
    ],
    defaultConfigCandidate: 'standard'
)]
private ?object $subscription = null;
```

### Raw Data Access: `rawDataItem('key')` and `rawDataItemExists('key')`

Access values from the raw data being used for hydration:

```php
#[Property(
    configCandidates: [
        ['id' => 'gasolineCar', 'when' => "expr(rawDataItemExists('gasoline_kind'))"],
        ['id' => 'electricCar', 'when' => "expr(rawDataItemExists('energy_provider'))"],
    ],
    defaultConfigCandidate: 'gasolineCar'
)]
private array $cars = [];
```

### Service Access: `service('service_id')` or `serviceId('service_id')`

Get a service from the service locator:

```php
#[DataSource(args: ["expr(service('my.service'))"])]
private ?string $data = null;

// Alternative syntax
#[DataSource(args: ["expr(serviceId('my.service'))"])]
private ?string $data = null;
```

### Data Source Access: `source('source_id')`

Get the result of a data source by its ID:

```php
#[DataSourcesStore([
    new MultiPropDataSource(id: 'personData', class: PersonService::class, method: 'getData'),
])]
class Person
{
    #[DataSource(args: ["expr(source('personData')['email'])"])]
    private ?string $email = null;
}
```

### Environment Variables: `envVar('KEY')` and `envVarExists('KEY')`

Access environment variables:

```php
// Get environment variable value
#[DataSource(args: ["expr(envVar('API_KEY'))"])]
private ?string $apiKey = null;

// Check if environment variable exists
#[SkipProperty(when: "expr(envVarExists('PRODUCTION'))")]
private ?string $debugInfo = null;
```

### Property Access: `property('propertyName')` and `strictProperty('propertyName')`

Explicit property access functions:

```php
// Uses getter if available
#[DataSource(args: ["expr(property('id'))"])]

// Direct access, bypasses getter
#[DataSource(args: ["expr(strictProperty('id'))"])]
```

### Object and Parent Object Functions

```php
// Get current object
#[DataSource(args: ["expr(object())"])]

// Get parent object
#[DataSource(args: ["expr(parentObject())"])]
```

## Conditional Expressions with `when`

The `when` key in `configCandidates`, `candidates`, and hydration control attributes allows conditional behavior:

### Conditional Property Inclusion/Exclusion

Use `when` in `SkipProperty` and `KeepProperty` to conditionally control hydration:

```php
use Kassko\DataMapper\Attribute\SkipProperty;
use Kassko\DataMapper\Attribute\KeepProperty;
use Kassko\DataMapper\Attribute\SkipAllProperties;

#[KeepAllProperties]  // Default behavior
class Entity
{
    private ?string $firstName = null;   // Always hydrated
    
    #[SkipProperty(when: "expr(contextKeyExists('hide_email'))")]
    private ?string $email = null;  // Skipped only if 'hide_email' context key exists
}
```

```php
#[SkipAllProperties]
class Entity
{
    #[KeepProperty(when: "expr(contextKeyExists('include_id'))")]
    private ?string $id = null;  // Kept only if 'include_id' context key exists
    
    private ?string $temp = null;  // Never hydrated
}
```

### Conditional Property Attribute with keepWhen

```php
#[SkipAllProperties]
class Entity
{
    #[Property(key: 'user_name', keepWhen: "expr(contextKeyExists('include_name'))")]
    private ?string $name = null;  // Property is active only if condition is true
}
```

### PropertyConfig Candidates

```php
#[PropertyConfigStore([
    new PropertyConfig(id: 'gasolineCar', class: GasolineCar::class),
    new PropertyConfig(id: 'electricCar', class: ElectricCar::class),
])]
class Garage
{
    #[Property(
        configCandidates: [
            ['id' => 'gasolineCar', 'when' => "expr(rawDataItemExists('gasoline_kind'))"],
            ['id' => 'electricCar', 'when' => "expr(rawDataItemExists('energy_provider'))"],
        ],
        defaultConfigCandidate: 'gasolineCar'
    )]
    private array $cars = [];
}
```

### DataSourceRef Candidates

```php
#[DataSourcesStore([
    new MultiPropDataSource(id: 'newFeatureSource', class: NewService::class, method: 'getData'),
    new MultiPropDataSource(id: 'oldFeatureSource', class: OldService::class, method: 'getData'),
])]
class Feature
{
    #[DataSourceRef(
        candidates: [
            ['id' => 'newFeatureSource', 'when' => "expr(context('new_feature_enabled'))"],
        ],
        defaultCandidate: ['id' => 'oldFeatureSource']
    )]
    private ?string $data = null;
}
```

### No-Op Default Configuration

To intentionally skip hydration when no candidate matches, use an empty array:

```php
#[Property(
    configCandidates: [
        ['id' => 'premium', 'when' => "expr(context('is_premium'))"],
    ],
    defaultConfigCandidate: []  // No-op: do nothing if no candidate matches
)]
private ?object $premiumFeature = null;
```

## Advanced: Context with Method Results

The `Context` attribute supports capturing method results into the context:

### Simple Key-Value Context

```php
#[Context(
    ['key' => 'staticValue', 'value' => 'hello'],
)]
class Person
{
    // context('staticValue') returns 'hello'
}
```

### Capturing Method Results

```php
#[Context(
    ['key' => 'personRawData', 'class' => 'person.data_source', 'method' => 'fetchData', 'args' => ['#id']],
)]
class Person
{
    #[Property(
        configCandidates: [
            ['id' => 'premium', 'when' => "expr(context('personRawData')['type'] === 'premium')"],
        ],
        defaultConfigCandidate: 'standard'
    )]
    private ?object $subscription = null;
}
```

### Chaining Context Dependencies

Context entries are evaluated in order, so later entries can depend on earlier ones:

```php
#[Context(
    ['key' => 'baseData', 'class' => 'base.source', 'method' => 'fetch', 'args' => ['#id']],
    ['key' => 'derivedData', 'class' => 'other.source', 'method' => 'process', 'args' => ["expr(context('baseData'))"]],
)]
class Entity
{
    // Both 'baseData' and 'derivedData' are available in expressions
}
```

## Using MethodAlias for Custom Functions

The `MethodAlias` attribute allows defining reusable method references that can be used in expressions:

```php
#[MethodAlias(name: 'personDataKeyExists', class: '##object', method: 'checkKeyExists')]
#[Context(
    ['key' => 'personData', 'class' => 'person.service', 'method' => 'fetchData', 'args' => ['#id']],
)]
class Person
{
    #[Property(
        configCandidates: [
            ['id' => 'personal', 'when' => "expr(personDataKeyExists(context('personData'), 'personal-email'))"],
            ['id' => 'professional', 'when' => "expr(personDataKeyExists(context('personData'), 'professional-email'))"],
        ],
        defaultConfigCandidate: []
    )]
    private ?string $email = null;
    
    private function checkKeyExists(array $data, string $key): bool
    {
        return isset($data[$key]);
    }
}
```

### Using MethodAlias in DataSource

```php
#[MethodAlias(name: 'fetchPerson', class: 'person.data_source', method: 'fetch')]
class Person
{
    #[DataSource(methodAlias: 'fetchPerson', args: ['#id'])]
    private ?string $email = null;
}
```

## Context Levels

DataMapper supports three context levels:

1. **Application Context**: Shared across all hydration sessions
2. **Hydration Context**: Shared within one hydration session  
3. **Object Instance Context**: Specific to one object instance (values may depend on object data)

The object instance context is particularly useful when context values depend on the specific object being hydrated, such as data fetched based on an object's ID.

## Expression Evaluation Order

1. Expressions are evaluated lazily when the property is accessed (for lazy loading) or during hydration (for eager loading)
2. Context entries in `#[Context(...)]` are evaluated in the order they are declared
3. Property references (`#propertyName`) trigger loading of that property if needed
4. `when` expressions are evaluated in order until one returns `true`

## Best Practices

1. **Keep expressions simple**: Complex logic should be in your data source methods, not in expressions
2. **Use MethodAlias for complex checks**: Instead of complex inline expressions, define methods on your class
3. **Order context entries carefully**: Ensure dependencies are declared before their dependents
4. **Use meaningful alias names**: `fetchPersonData` is clearer than `fpd`
5. **Document your expressions**: Add PHPDoc comments explaining what expressions do
6. **Test expressions**: Write tests for different context/data scenarios

## Migration from `rule` to `when`

In previous versions, conditional expressions used the `rule` key. This has been renamed to `when` for clarity:

```php
// Old (deprecated)
['id' => 'config', 'rule' => "expr(...)"]

// New
['id' => 'config', 'when' => "expr(...)"]
```
