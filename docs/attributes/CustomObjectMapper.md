# CustomObjectMapper Attribute

The `CustomObjectMapper` attribute allows you to define a custom mapping strategy for a property when mapping from DTO sources, bypassing the standard mapping process.

## Scope

- **Target:** Properties only
- **Repeatable:** No

## Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `key` | string | Yes | - | Identifier for the custom mapper (registered via DataMapperBuilder) |
| `inputClass` | string\|null | No | null | Expected input type (for validation) |
| `outputClass` | string\|null | No | null | Expected output class (for type validation) |
| `nullableInput` | bool | No | true | Whether the input can be null |
| `nullableOutput` | bool | No | true | Whether the output can be null |
| `cascade` | bool | No | true | Whether this attribute cascades to child classes |
| `enabled` | bool | No | true | Whether this attribute is active |

## Usage

### 1. Define the Custom Object Mapper

```php
use Kassko\DataMapper\DataMapperBuilder;

$builder = new DataMapperBuilder();

// Register a custom object mapper
$builder->addCustomObjectMapper('address_mapper', function(object $source): ?Address {
    // Custom logic to transform DTO to domain object
    return new Address(
        street: $source->street,
        city: $source->city,
        formattedAddress: sprintf('%s, %s', $source->street, $source->city)
    );
});

$mapper = $builder->build();
```

### 2. Apply to a Property

```php
use Kassko\DataMapper\Attribute\CustomObjectMapper;

class Person
{
    #[CustomObjectMapper(
        key: 'address_mapper',
        inputClass: AddressDto::class,
        outputClass: Address::class
    )]
    private ?Address $address = null;

    public function getAddress(): ?Address
    {
        return $this->address;
    }
}
```

## Object Mapper Callable Signature

```php
function(object $source): ?object
```

- **Parameter:** `$source` - The source object (DTO) to map from
- **Returns:** The mapped object, or `null` if mapping fails/skips

## Type Validation

### Input Validation

When `inputClass` is specified:
- The source object is validated against this class
- Throws `RuntimeException` if type doesn't match and `nullableInput` is false

### Output Validation

When `outputClass` is specified:
- The mapper's return value is validated against this class
- Returns must be instances of the specified class (or null if `nullableOutput` is true)
- Throws `RuntimeException` if type doesn't match

```php
#[CustomObjectMapper(
    key: 'user_mapper',
    inputClass: UserDto::class,    // Validates input is UserDto
    outputClass: User::class,      // Validates output is User or null
    nullableOutput: false          // Output must not be null
)]
private User $user;
```

## Nullable Configuration

### nullableInput
- `true` (default): If source value is null, mapper is not called, property is set to null
- `false`: Source value must be present, throws exception if null

### nullableOutput
- `true` (default): Mapper can return null
- `false`: Mapper must return a non-null value, throws exception if null

```php
// Strict mapping: input required, output required
#[CustomObjectMapper(
    key: 'payment_mapper',
    nullableInput: false,
    nullableOutput: false
)]
private Payment $payment;

// Lenient mapping: input optional, output optional  
#[CustomObjectMapper(
    key: 'metadata_mapper',
    nullableInput: true,
    nullableOutput: true
)]
private ?Metadata $metadata = null;
```

## Use Cases

### 1. Complex Type Discrimination

```php
$builder->addCustomObjectMapper('content_mapper', function(object $source): Content {
    return match($source->type) {
        'article' => new Article($source->title, $source->body),
        'video' => new Video($source->title, $source->url),
        'image' => new Image($source->title, $source->path),
        default => throw new \InvalidArgumentException("Unknown type: {$source->type}"),
    };
});
```

### 2. Value Object Creation

```php
$builder->addCustomObjectMapper('money_mapper', function(object $source): Money {
    return new Money(
        amount: $source->amount,
        currency: Currency::from($source->currencyCode)
    );
});
```

### 3. Aggregate Building

```php
$builder->addCustomObjectMapper('order_mapper', function(object $source): Order {
    $order = new Order($source->id);
    
    foreach ($source->items as $itemDto) {
        $order->addItem(new OrderItem(
            product: $itemDto->product,
            quantity: $itemDto->quantity,
            price: $itemDto->price
        ));
    }
    
    return $order;
});
```

### 4. External Service Integration

```php
$builder->addCustomObjectMapper('user_mapper', function(object $source) use ($userService): User {
    // Enrich DTO data with external service data
    $enrichedData = $userService->enrichUserData($source->id);
    
    return new User(
        id: $source->id,
        name: $source->name,
        permissions: $enrichedData['permissions'],
        roles: $enrichedData['roles']
    );
});
```

## Retrieving Custom Mappers

You can retrieve registered custom object mappers from the DataMapper:

```php
$dataMapper = $builder->build();

// Get a specific custom object mapper
$addressMapper = $dataMapper->getCustomObjectMapper('address_mapper');

if ($addressMapper !== null) {
    $address = $addressMapper($addressDto);
}
```

## Comparison with CustomHydrator

| Feature | CustomHydrator | CustomObjectMapper |
|---------|----------------|-------------------|
| Input | Array (raw data) | Object (DTO) |
| Use case | API responses, JSON | DTO transformations |
| Validation | `objectClass` | `inputClass`, `outputClass` |
| Nullability | Implicit | Explicit (`nullableInput`, `nullableOutput`) |

## See Also

- [ObjectMapper](../classes/ObjectMapper.md) - ObjectMapper class documentation
- [CustomHydrator](CustomHydrator.md) - For custom hydration from arrays
- [Property](Property.md) - Property mapping configuration
- [DataMapperBuilder](../classes/DataMapperBuilder.md) - Builder configuration
