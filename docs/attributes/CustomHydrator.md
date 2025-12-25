# CustomHydrator Attribute

The `CustomHydrator` attribute allows you to define a custom hydration strategy for a property, bypassing the standard hydration process.

## Scope

- **Target:** Properties only
- **Repeatable:** No

## Parameters

- `key` (string, required): Identifier for the custom hydrator (registered via DataMapperBuilder)
- `objectClass` (string|null): Optional expected class for type validation

## Usage

### 1. Define the Custom Hydrator

```php
use Kassko\DataMapper\DataMapperBuilder;

$builder = new DataMapperBuilder();

// Register a custom hydrator
$builder->addCustomHydrator('complex_parser', function(array $data): ?object {
    // Custom logic to create and hydrate an object
    if (!isset($data['type'])) {
        return null;
    }
    
    return match($data['type']) {
        'A' => new TypeA($data),
        'B' => new TypeB($data),
        default => null,
    };
});

$mapper = $builder->build();
```

### 2. Apply to a Property

```php
use Kassko\DataMapper\Attribute\CustomHydrator;

class Document
{
    #[CustomHydrator(
        key: 'complex_parser',
        objectClass: BaseType::class
    )]
    private ?BaseType $content = null;

    public function getContent(): ?BaseType
    {
        return $this->content;
    }
}
```

## Hydrator Callable Signature

```php
function(array $data): ?object
```

- **Parameter:** `$data` - Raw data array for hydration
- **Returns:** The hydrated object, or `null` if hydration fails/skips

## Type Validation

When `objectClass` is specified:
- The hydrator's return value is validated against this class
- Returns must be instances of the specified class (or null)
- Throws `RuntimeException` if type doesn't match

```php
#[CustomHydrator(
    key: 'user_parser',
    objectClass: User::class  // Validates return is User or null
)]
private ?User $user = null;
```

## Attribute Exclusivity

`CustomHydrator` **cannot** be combined with:
- `Property`
- `PropertyCandidates`
- `DataSource`
- `DataSourceRef`
- `SinglePropDataSource`
- `MultiPropDataSource`

Attempting to combine these will throw an `InvalidArgumentException`.

## Use Cases

1. **Complex Type Discrimination**
   ```php
   // When PropertyCandidates isn't sufficient
   $builder->addCustomHydrator('smart_parser', function($data) {
       // Complex logic to determine type
       if ($data['version'] > 2 && $data['format'] === 'new') {
           return new NewFormat($data);
       }
       return new LegacyFormat($data);
   });
   ```

2. **Third-Party Object Creation**
   ```php
   // When you can't control object construction
   $builder->addCustomHydrator('dto_factory', function($data) {
       return DTOFactory::createFromArray($data);
   });
   ```

3. **Complex Validation**
   ```php
   $builder->addCustomHydrator('validated_entity', function($data) {
       $entity = new Entity();
       // Complex validation and transformation
       if (!Validator::validate($data)) {
           return null;  // Skip hydration
       }
       $entity->setData($data);
       return $entity;
   });
   ```

4. **Legacy Data Migration**
   ```php
   $builder->addCustomHydrator('migrator', function($data) {
       // Handle multiple legacy formats
       return LegacyMigrator::transform($data);
   });
   ```

## Known Limitations

### Interaction with Loading Attribute

The `Loading` attribute may not function correctly with `CustomHydrator` because:
- Custom hydrators control the entire hydration process
- Depth management is handled within the custom hydrator logic
- This is an accepted limitation for v2.0

If you need depth control with custom hydration, implement it within your custom hydrator callable.

## Error Handling

### Hydrator Not Found
```php
// Throws RuntimeException
#[CustomHydrator(key: 'nonexistent')]
private $property;
```
**Solution:** Register the hydrator before building the DataMapper.

### Type Mismatch
```php
#[CustomHydrator(key: 'parser', objectClass: User::class)]
private $user;

// Hydrator returns Product -> RuntimeException
$builder->addCustomHydrator('parser', fn($data) => new Product());
```

### Conflicting Attributes
```php
// Throws InvalidArgumentException
#[CustomHydrator(key: 'parser')]
#[Property(class: User::class)]  // Conflict!
private $user;
```

## Notes

- Custom hydrators are registered globally on the DataMapperBuilder
- The same hydrator can be reused across multiple properties
- Custom hydrators receive the full raw data array (not filtered by property)
- Returning `null` from a hydrator is valid and will set the property to `null`
- Custom hydrators are called during lazy loading if the property uses `Loading` attribute
