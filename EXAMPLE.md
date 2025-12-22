# Data Mapper Examples

## Basic Usage

### Simple Property Mapping

```php
use Kassko\DataMapper\Attribute\Property;

class Person
{
    #[Property(name: 'first_name')]
    private ?string $firstName = null;
    
    #[Property(name: 'last_name')]
    private ?string $lastName = null;
}

// Hydration
$rawData = ['first_name' => 'John', 'last_name' => 'Doe'];
$person = $hydrator->hydrate(Person::class, $rawData);
```

## Lazy Loading

### Single-Property Data Source

Use `DataSource` or `SinglePropDataSource` when each property has its own data source:

```php
use Kassko\DataMapper\Attribute\SinglePropDataSource;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

class User
{
    use LoadableTrait;
    
    private int $id;
    
    #[SinglePropDataSource(
        class: UserNameService::class,
        method: 'getName',
        args: ['#id']
    )]
    private ?string $name = null;
    
    #[SinglePropDataSource(
        class: UserAvatarService::class,
        method: 'getAvatar',
        args: ['#id']
    )]
    private ?string $avatar = null;
    
    public function getName(): ?string
    {
        $this->loadProperty('name');
        return $this->name;
    }
}
```

### Multi-Property Data Source

Use `MultiPropDataSource` when multiple properties come from the same source:

```php
use Kassko\DataMapper\Attribute\MultiPropDataSource;
use Kassko\DataMapper\Attribute\DataSourceRef;
use Kassko\DataMapper\Attribute\Property;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

#[MultiPropDataSource(
    id: 'personData',
    class: PersonDataSource::class,
    method: 'findById',
    args: ['#id']
)]
class Person
{
    use LoadableTrait;
    
    private int $id;
    
    #[DataSourceRef(id: 'personData')]
    #[Property(name: 'first_name')]
    private ?string $firstName = null;
    
    #[DataSourceRef(id: 'personData')]
    #[Property(name: 'last_name')]
    private ?string $lastName = null;
    
    #[DataSourceRef(id: 'personData')]
    private ?string $email = null;
    
    public function __construct(int $id)
    {
        $this->id = $id;
    }
    
    public function getFirstName(): ?string
    {
        $this->loadProperty('firstName');
        return $this->firstName;
    }
}
```

### Mixed Single and Multi-Property Sources

```php
use Kassko\DataMapper\Attribute\MultiPropDataSource;
use Kassko\DataMapper\Attribute\SinglePropDataSource;
use Kassko\DataMapper\Attribute\DataSourceRef;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

#[MultiPropDataSource(
    id: 'personData',
    class: PersonDataSource::class,
    method: 'findById',
    args: ['#id']
)]
class Person
{
    use LoadableTrait;
    
    private int $id;
    
    // From multi-property source
    #[DataSourceRef(id: 'personData')]
    private ?string $firstName = null;
    
    #[DataSourceRef(id: 'personData')]
    private ?string $lastName = null;
    
    // From single-property source
    #[SinglePropDataSource(
        class: AvatarService::class,
        method: 'getAvatar',
        args: ['#id']
    )]
    private ?string $avatar = null;
}
```

## Advanced Features

### DataSource Chaining (Fallback)

```php
use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\Attribute\SinglePropDataSource;
use Kassko\DataMapper\Attribute\DataSourceRef;

#[DataSourcesStore([
    new SinglePropDataSource(id: 'cache', class: CacheSource::class, method: 'get'),
    new SinglePropDataSource(id: 'database', class: DbSource::class, method: 'find'),
    new SinglePropDataSource(id: 'api', class: ApiSource::class, method: 'fetch'),
])]
class Product
{
    #[DataSourceRef(
        chain: ['cache', 'database', 'api'],
        exception: NotFoundException::class
    )]
    private ?ProductData $data = null;
}
```

### DataSource Aggregation

```php
#[DataSourceRef(
    providers: ['basicInfo', 'extendedInfo', 'metaInfo']
)]
private array $fullProfile = [];
```

### Instance Mapping

```php
$rawData = [
    'billing_street' => '123 Main St',
    'billing_city' => 'NYC',
    'delivery_street' => '456 Oak Ave',
    'delivery_city' => 'LA',
];

class Order
{
    #[Property(
        class: Address::class,
        mapping: ['billing_street' => 'street', 'billing_city' => 'city']
    )]
    private ?Address $billingAddress = null;
    
    #[Property(
        class: Address::class,
        mapping: ['delivery_street' => 'street', 'delivery_city' => 'city']
    )]
    private ?Address $deliveryAddress = null;
}
```

### Polymorphic Collections

```php
class Garage
{
    #[PropertyCandidates([
        new PropertyCandidate(
            discriminator: "expr(rawDataItemExists('fuel_type'))",
            property: new Property(class: GasolineCar::class)
        ),
        new PropertyCandidate(
            discriminator: "expr(rawDataItemExists('battery_capacity'))",
            property: new Property(class: ElectricCar::class)
        )
    ])]
    private array $vehicles = [];
}

$data = [
    'vehicles' => [
        ['id' => 1, 'fuel_type' => 'diesel'],
        ['id' => 2, 'battery_capacity' => '100kWh'],
    ]
];
// Results in [GasolineCar, ElectricCar]
```

### Lifecycle Hooks

```php
#[Hook(name: 'after_create_object', method: 'init', args: ['##object'])]
class Entity
{
    #[Hook(name: 'before_set_property', method: 'validate')]
    #[Hook(name: 'after_set_property', method: 'log', args: ['##object', '#name'])]
    private ?string $name = null;
    
    public function init(self $entity): void
    {
        // Called after instantiation
    }
    
    public function validate(): void
    {
        // Called before setting name
    }
    
    public function log(self $entity, ?string $name): void
    {
        // Called after setting name
    }
}
```

### Expression Language

```php
#[DataSource(
    class: ComplexService::class,
    method: 'process',
    args: [
        '#id',                              // Property via getter
        '!#internalId',                     // Property direct access
        '##object',                         // Current object
        "expr(source('otherSource')['key'])",  // Other DataSource result
        "expr(context('tenant_id'))",       // Context value
        "expr(envVar('API_KEY'))",          // Environment variable
        "expr(strictProperty('secret'))",   // Direct property access
    ]
)]
private ?Result $result = null;
```

## Hook with External Service

```php
use Kassko\DataMapper\Attribute\Hook;
use Kassko\DataMapper\Attribute\DataSource;
use Kassko\DataMapper\Attribute\DataSourceRef;

// Define a validation service
class ValidationService
{
    public function validateEmail(object $obj, ?string $email): void
    {
        if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Invalid email: {$email}");
        }
    }
    
    public function validateAge(object $obj, ?int $age): void
    {
        if ($age && ($age < 0 || $age > 150)) {
            throw new \InvalidArgumentException("Invalid age: {$age}");
        }
    }
}

// Use the service in hooks
#[DataSource(
    id: 'personSource',
    class: PersonDataSource::class,
    method: 'getData',
    args: ['#id'],
    supplySeveralProperties: true
)]
class Person
{
    use LoadableTrait;
    
    private int $id;
    
    #[DataSourceRef(id: 'personSource')]
    #[Hook(
        name: Hook::AFTER_SET_PROPERTY,
        class: ValidationService::class,  // External service
        method: 'validateEmail',
        args: ['##object', '#email']
    )]
    private ?string $email = null;
    
    #[DataSourceRef(id: 'personSource')]
    #[Hook(
        name: Hook::AFTER_SET_PROPERTY,
        class: ValidationService::class,
        method: 'validateAge',
        args: ['##object', '#age']
    )]
    private ?int $age = null;
}
```

## Property Dependencies with Needs

### Understanding Auto-Loading vs Needs

**Auto-Loading (No Needs Required)**: Properties passed as arguments (`#propX`) are automatically loaded BEFORE being passed to the DataSource.

**Needs Attribute**: Use when properties must be loaded for reasons OTHER than being passed as arguments (e.g., used in getter, validation, hooks).

### Example 1: Auto-Loading in Args (No Needs Required)

```php
use Kassko\DataMapper\Attribute\DataSource;
use Kassko\DataMapper\Attribute\DataSourceRef;

#[DataSource(id: 'userSource', class: UserDataSource::class, method: 'getUser', args: ['#userId'], supplySeveralProperties: true)]
#[DataSource(id: 'preferencesSource', class: PreferencesDataSource::class, method: 'getPreferences', args: ['#userId', '#role'], supplySeveralProperties: true)]
class UserProfile
{
    use LoadableTrait;
    
    private int $userId;
    
    #[DataSourceRef(id: 'userSource')]
    private ?string $role = null;
    
    // NO Needs required - userId and role auto-load because they're in args
    #[DataSourceRef(id: 'preferencesSource')]
    private ?array $preferences = null;
    
    public function getPreferences(): ?array
    {
        $this->loadProperty('preferences');
        // userId and role were already auto-loaded when passed as args
        return $this->preferences;
    }
}
```

### Example 2: Needs for Properties Used in Getter

```php
use Kassko\DataMapper\Attribute\Needs;
use Kassko\DataMapper\Attribute\DataSource;
use Kassko\DataMapper\Attribute\DataSourceRef;

#[DataSource(id: 'discountSource', class: DiscountDataSource::class, method: 'getDiscount', args: ['#customerId'], supplySeveralProperties: true)]
#[DataSource(id: 'customerSource', class: CustomerDataSource::class, method: 'getCustomer', args: ['#customerId'], supplySeveralProperties: true)]
#[DataSource(id: 'orderSource', class: OrderDataSource::class, method: 'getOrder', args: ['#orderId'], supplySeveralProperties: true)]
class Order
{
    use LoadableTrait;
    
    private int $customerId;
    private int $orderId;
    
    #[DataSourceRef(id: 'customerSource')]
    private ?Customer $customer = null;
    
    #[DataSourceRef(id: 'discountSource')]
    private ?float $discount = null;
    
    // orderId auto-loads (it's in args)
    // customer and discount need Needs (they're used in the getter, not in args)
    #[Needs(['customer', 'discount'])]
    #[DataSourceRef(id: 'orderSource')]
    private ?float $totalPrice = null;
    
    public function getTotalPrice(): ?float
    {
        $this->loadProperty('totalPrice');
        
        // customer and discount are used here - loaded via Needs
        if ($this->customer->isPremium()) {
            return $this->totalPrice * (1 - $this->discount);
        }
        return $this->totalPrice;
    }
}
```

### Example 3: Combining Auto-Loading and Needs

```php
use Kassko\DataMapper\Attribute\Needs;
use Kassko\DataMapper\Attribute\DataSource;
use Kassko\DataMapper\Attribute\DataSourceRef;

#[DataSource(id: 'sourceA', class: SourceA::class, method: 'getData', supplySeveralProperties: true)]
#[DataSource(id: 'sourceB', class: SourceB::class, method: 'getData', supplySeveralProperties: true)]
#[DataSource(id: 'sourceC', class: SourceC::class, method: 'process', args: ['#propA'], supplySeveralProperties: true)]
class Example
{
    use LoadableTrait;
    
    #[DataSourceRef(id: 'sourceA')]
    private ?string $propA = null;
    
    #[DataSourceRef(id: 'sourceB')]
    private ?bool $validator = null;
    
    // propA auto-loads (it's in args)
    // validator needs Needs (it's used in the getter, not in args)
    #[Needs(['validator'])]
    #[DataSourceRef(id: 'sourceC')]
    private ?string $propC = null;
    
    public function getPropC(): ?string
    {
        $this->loadProperty('propC');
        
        // validator is used here - loaded via Needs
        if (!$this->validator) {
            throw new \RuntimeException('Validation failed');
        }
        
        return $this->propC;
    }
}
```

### Key Takeaways

1. **Properties in args auto-load** - No `Needs` required
2. **Use `Needs` for properties used in getter** - They're not in args but needed for logic
3. **Already-loaded properties are not reloaded** - Efficient and safe
