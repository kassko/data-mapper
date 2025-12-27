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
use Kassko\DataMapper\Attribute\PropertyInstantiatingHook;
use Kassko\DataMapper\Attribute\PropertySettingHook;
use Kassko\DataMapper\Attribute\PropertyHydratingHook;

#[PropertyInstantiatingHook(after_instantiating: 'init', args: ['##object'])]
#[PropertyHydratingHook(
    before_hydrate_object: 'prepare',
    after_hydrate_object: 'finalize'
)]
class Entity
{
    #[PropertySettingHook(
        before_set_property: 'validate',
        after_set_property: 'log',
        args: ['##object', '#name']
    )]
    private ?string $name = null;
    
    public function init(self $entity): void
    {
        // Called after instantiation
    }
    
    public function prepare(array $rawData): void
    {
        // Called before hydration starts
    }
    
    public function validate(self $entity, ?string $name): void
    {
        // Called before setting name
    }
    
    public function log(self $entity, ?string $name): void
    {
        // Called after setting name
    }
    
    public function finalize(?object $object, array $rawData): void
    {
        // Called after hydration completes
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
use Kassko\DataMapper\Attribute\PropertySettingHook;
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
    #[PropertySettingHook(
        after_set_property: 'validateEmail',
        class: ValidationService::class,  // External service
        args: ['##object', '#email']
    )]
    private ?string $email = null;
    
    #[DataSourceRef(id: 'personSource')]
    #[PropertySettingHook(
        after_set_property: 'validateAge',
        class: ValidationService::class,
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


## v2.0 Features

### Priority-Based Hydration

Control which data sources take precedence when multiple sources can hydrate the same property:

```php
use Kassko\DataMapper\Attribute\DataSource;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

class Product
{
    use LoadableTrait;
    
    private int $id = 1;
    
    // Load from cache first (priority: 0)
    #[DataSource(
        class: CacheService::class,
        method: 'getPrice',
        args: ['#id'],
        priority: 0
    )]
    private ?float $price = null;
    
    // This will override the cached price (priority: 10)
    #[DataSource(
        class: ApiService::class,
        method: 'getCurrentPrice',
        args: ['#id'],
        priority: 10
    )]
    private ?float $price = null;
    
    public function getPrice(): ?float
    {
        $this->loadProperty('price');
        return $this->price;
    }
}

// Usage
$product = new Product();
echo $product->getPrice(); // Loads from cache, then overrides with API value
```

### Fallback Pattern

Use fallbacks for graceful degradation:

```php
use Kassko\DataMapper\Attribute\DataSourceRef;
use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\Attribute\SinglePropDataSource;
use Kassko\DataMapper\Exception\NoValidDataSourceException;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

#[DataSourcesStore([
    new SinglePropDataSource(
        id: 'primaryApi',
        class: PrimaryApiService::class,
        method: 'getData'
    ),
    new SinglePropDataSource(
        id: 'cacheBackup',
        class: CacheService::class,
        method: 'getCached'
    ),
    new SinglePropDataSource(
        id: 'defaultValues',
        class: DefaultsService::class,
        method: 'getDefaults'
    ),
])]
class Config
{
    use LoadableTrait;
    
    // Try primary API, then cache, then defaults
    #[DataSourceRef(
        id: 'primaryApi',
        fallbacks: ['cacheBackup', 'defaultValues'],
        exceptionOnNoValidDataSource: NoValidDataSourceException::class,
        priority: 5
    )]
    private ?array $settings = null;
    
    public function getSettings(): ?array
    {
        $this->loadProperty('settings');
        return $this->settings;
    }
}
```

### Provider Aggregation

Merge data from multiple sources:

```php
use Kassko\DataMapper\Attribute\DataSourceRef;
use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\Attribute\MultiPropDataSource;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

#[DataSourcesStore([
    new MultiPropDataSource(
        id: 'systemDefaults',
        class: DefaultsProvider::class,
        method: 'getDefaults'
    ),
    new MultiPropDataSource(
        id: 'userPreferences',
        class: UserProvider::class,
        method: 'getPreferences',
        args: ['#userId']
    ),
    new MultiPropDataSource(
        id: 'runtimeOverrides',
        class: RuntimeProvider::class,
        method: 'getOverrides'
    ),
])]
class ApplicationConfig
{
    use LoadableTrait;
    
    private int $userId = 123;
    
    // Aggregates all three providers (later providers override earlier ones)
    #[DataSourceRef(
        providers: ['systemDefaults', 'userPreferences', 'runtimeOverrides']
    )]
    private ?array $config = null;
    
    public function getConfig(): ?array
    {
        $this->loadProperty('config');
        return $this->config;
    }
}
```

### Build-Time Validation

Validate your metadata before runtime:

```bash
# Validate a single class
./bin/datamapper datamapper:validate:class 'App\Entity\User'

# Validate all classes in a directory
./bin/datamapper datamapper:validate src/Entity

# With namespace option
./bin/datamapper datamapper:validate src/Entity --namespace='App\Entity'

# Fail on warnings
./bin/datamapper datamapper:validate src/Entity --fail-on-warning
```

Example validation output:

```
DataMapper Metadata Validation
==============================

 Found 15 classe(s) to validate

App\Entity\User
  ✗ App\Entity\User::$email: References source 'emailValidator' which does not exist in DataSourcesStore.
  ⚠ App\Entity\User: DataSourcesStore source at index 2 has no id. It cannot be referenced via DataSourceRef.

Summary
-------

 Total classes: 15
 Valid: 14
 Invalid: 1
 With warnings: 1

[ERROR] Validation failed!
```

## Migration from v1.x to v2.0

### Chain → Fallbacks

```php
// v1.x (DEPRECATED)
#[DataSourceRef(
    chain: ['primary', 'backup'],
    exceptionOnNoValidDataSource: MyException::class
)]

// v2.0
#[DataSourceRef(
    id: 'primary',
    fallbacks: ['backup'],
    exceptionOnNoValidDataSource: MyException::class
)]
```

### Priority Addition

All DataSource attributes now support priority (default: 0):

```php
// Add priority to control hydration precedence
#[DataSource(
    class: MyService::class,
    method: 'getData',
    priority: 10  // Higher priority overrides lower priority
)]
```

## Context

### Hydration Context with Named Arguments

Pass contextual information through the object hierarchy:

```php
use Kassko\DataMapper\Attribute\Context;

class Company
{
    #[Context(
        company_type: 'enterprise',
        region: 'europe',
        tier: 'premium'
    )]
    private Chief $chief;
}

class Chief
{
    // Access parent context in expressions
    #[PropertyCandidates([
        new PropertyCandidate(
            discriminator: "expr(context('tier') === 'premium')",
            property: new Property(class: PremiumOffice::class)
        ),
        new PropertyCandidate(
            discriminator: "expr(contextKeyExists('region'))",
            property: new Property(class: RegionalOffice::class)
        )
    ])]
    private ?Office $office = null;
    
    // Override context for nested objects
    #[Context(tier: 'standard')]  // Overrides 'premium' from parent
    private ?Department $department = null;
}
```

### Application Context

Set context values from your application before hydration:

```php
use Kassko\DataMapper\DataMapper;

$dataMapper = new DataMapper($serviceResolver);

// Add feature flags, user info, environment variables
$dataMapper->addToContext('new_api_enabled', $featureFlag->isEnabled());
$dataMapper->addToContext('current_user_role', $user->getRole());

// Or add multiple at once
$dataMapper->addManyToContext([
    'env' => 'production',
    'debug' => false,
    'api_version' => 'v2',
]);

// Access in entities
class User
{
    #[SinglePropDataSource(
        class: ProfileService::class,
        method: 'getProfile',
        args: "expr(context('new_api_enabled') ? ['v2', #id] : ['v1', #id])"
    )]
    private ?Profile $profile = null;
}
```

## Data Lineage Collection

Track data flow during hydration for debugging:

```php
use Kassko\DataMapper\DataMapper;
use Kassko\DataMapper\DataCollector\LineageEvent;

$dataMapper = new DataMapper($serviceResolver);

// Enable lineage collection
$dataMapper->enableLineageCollection();

// Perform hydration
$user = new User(123);
$user->getName();
$user->getProfile();

// Get lineage information
$collector = $dataMapper->getLineageCollector();

// Get all events
$events = $collector->getEvents();
foreach ($events as $event) {
    echo sprintf(
        "[%s] %s::%s - %s\n",
        $event->type,
        $event->objectClass,
        $event->propertyName,
        $event->reason ?? 'OK'
    );
}

// Get summary
$summary = $collector->getSummary();
print_r($summary);
// Output:
// [
//     'totalEvents' => 15,
//     'eventsByType' => [
//         'datasource_call' => 5,
//         'property_hydration' => 8,
//         'property_skipped' => 2,
//     ],
//     'skippedReasons' => ['locked' => 1, 'priority' => 1],
//     'maxDepth' => 3,
// ]

// Get skipped properties
$skipped = $collector->getEventsByType(LineageEvent::TYPE_PROPERTY_SKIPPED);
foreach ($skipped as $event) {
    echo sprintf(
        "Skipped %s::%s - Reason: %s\n",
        $event->objectClass,
        $event->propertyName,
        $event->reason
    );
}

// Disable when done
$dataMapper->disableLineageCollection();
$collector->clear();
```
