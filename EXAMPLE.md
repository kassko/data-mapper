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

### Lazy Loading with DataSource

```php
use Kassko\DataMapper\Attribute\DataSource;
use Kassko\DataMapper\Attribute\DataSourceRef;
use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

#[DataSourcesStore([
    new DataSource(
        id: 'personData',
        class: PersonDataSource::class,
        method: 'findById',
        args: ['#id'],
        supplySeveralProperties: true
    )
])]
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

## Advanced Features

### DataSource Chaining (Fallback)

```php
#[DataSourcesStore([
    new DataSource(id: 'cache', class: CacheSource::class, method: 'get'),
    new DataSource(id: 'database', class: DbSource::class, method: 'find'),
    new DataSource(id: 'api', class: ApiSource::class, method: 'fetch'),
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
#[Hook(name: 'after_create_object', method: 'init', args: ['##this'])]
class Entity
{
    #[Hook(name: 'before_set_property', method: 'validate')]
    #[Hook(name: 'after_set_property', method: 'log', args: ['##this', '#name'])]
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
        '##this',                           // Current object
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
        args: ['##this', '#email']
    )]
    private ?string $email = null;
    
    #[DataSourceRef(id: 'personSource')]
    #[Hook(
        name: Hook::AFTER_SET_PROPERTY,
        class: ValidationService::class,
        method: 'validateAge',
        args: ['##this', '#age']
    )]
    private ?int $age = null;
}
```

## Property Dependencies with Needs

```php
use Kassko\DataMapper\Attribute\Needs;
use Kassko\DataMapper\Attribute\DataSource;
use Kassko\DataMapper\Attribute\DataSourceRef;

#[DataSource(id: 'userSource', class: UserDataSource::class, method: 'getUser', args: ['#userId'], supplySeveralProperties: true)]
#[DataSource(id: 'profileSource', class: ProfileDataSource::class, method: 'getProfile', args: ['#userId'], supplySeveralProperties: true)]
#[DataSource(id: 'preferencesSource', class: PreferencesDataSource::class, method: 'getPreferences', args: ['#userId', '#role'], supplySeveralProperties: true)]
class UserProfile
{
    use LoadableTrait;
    
    private int $userId;
    
    #[DataSourceRef(id: 'userSource')]
    private ?string $username = null;
    
    #[DataSourceRef(id: 'userSource')]
    private ?string $role = null;
    
    #[DataSourceRef(id: 'profileSource')]
    private ?string $avatar = null;
    
    // Load userId and role first, then load preferences
    #[Needs(['userId', 'role'])]
    #[DataSourceRef(id: 'preferencesSource')]
    private ?array $preferences = null;
    
    public function getPreferences(): ?array
    {
        $this->loadProperty('preferences');
        return $this->preferences;
    }
}
```

In this example:
1. When `getPreferences()` is called, the Needs attribute ensures `userId` and `role` are loaded first
2. The preferences DataSource can safely use `#userId` and `#role` as arguments
3. Already-loaded properties are not reloaded
