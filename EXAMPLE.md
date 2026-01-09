# DataMapper Examples

## Basic Usage

### Simple Property Mapping

```php
use Kassko\DataMapper\Attribute\Property;

class Person
{
    #[Property(sourceField: 'first_name')]
    private ?string $firstName = null;
    
    #[Property(sourceField: 'last_name')]
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
    #[Property(sourceField: 'first_name')]
    private ?string $firstName = null;
    
    #[DataSourceRef(id: 'personData')]
    #[Property(sourceField: 'last_name')]
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
        id: 'cache',
        fallbacks: ['database', 'api'],
        exceptionOnNoValidFallback: NotFoundException::class
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

### DataSource Candidates (Expression-Based Selection)

Select a data source dynamically based on context or conditions. When no expression matches, `defaultCandidate` is used:

```php
use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\Attribute\MultiPropDataSource;
use Kassko\DataMapper\Attribute\DataSourceRef;

#[DataSourcesStore([
    new MultiPropDataSource(id: 'newFeatureSource', class: NewFeatureSource::class, method: 'getData'),
    new MultiPropDataSource(id: 'oldFeatureSource', class: OldFeatureSource::class, method: 'getData'),
])]
class User
{
    #[DataSourceRef(
        candidates: [
            // Candidate: selected if new_feature_enabled context is true
            ['id' => 'newFeatureSource', 'when' => "expr(context('new_feature_enabled'))", 'priority' => 15],
        ],
        // Default: used when no expression matches
        defaultCandidate: ['id' => 'oldFeatureSource'],
        priority: 10
    )]
    private ?string $name = null;
}

// Usage with context
$dataMapper->addToContext('new_feature_enabled', true);
// -> Will use 'newFeatureSource' with priority 15

$dataMapper->addToContext('new_feature_enabled', false);
// -> Will use 'oldFeatureSource' (defaultCandidate) with base priority 10
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
use Kassko\DataMapper\Attribute\PropertyConfig;
use Kassko\DataMapper\Attribute\PropertyConfigStore;

#[PropertyConfigStore([
    new PropertyConfig(id: 'gasolineCar', class: GasolineCar::class),
    new PropertyConfig(id: 'electricCar', class: ElectricCar::class),
])]
class Garage
{
    #[Property(
        configCandidates: [
            ['id' => 'gasolineCar', 'when' => "expr(rawDataItemExists('fuel_type'))"],
            ['id' => 'electricCar', 'when' => "expr(rawDataItemExists('battery_capacity'))"],
        ],
        defaultConfigCandidate: 'gasolineCar'
    )]
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
#[DataSourcesStore([
    new MultiPropDataSource(
        id: 'personSource',
        class: PersonDataSource::class,
        method: 'getData',
        args: ['#id']
    )
])]
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
use Kassko\DataMapper\Attribute\MultiPropDataSource;
use Kassko\DataMapper\Attribute\DataSourceRef;
use Kassko\DataMapper\Attribute\DataSourcesStore;

#[DataSourcesStore([
    new MultiPropDataSource(id: 'userSource', class: UserDataSource::class, method: 'getUser', args: ['#userId']),
    new MultiPropDataSource(id: 'preferencesSource', class: PreferencesDataSource::class, method: 'getPreferences', args: ['#userId', '#role'])
])]
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
use Kassko\DataMapper\Attribute\MultiPropDataSource;
use Kassko\DataMapper\Attribute\DataSourceRef;
use Kassko\DataMapper\Attribute\DataSourcesStore;

#[DataSourcesStore([
    new MultiPropDataSource(id: 'discountSource', class: DiscountDataSource::class, method: 'getDiscount', args: ['#customerId']),
    new MultiPropDataSource(id: 'customerSource', class: CustomerDataSource::class, method: 'getCustomer', args: ['#customerId']),
    new MultiPropDataSource(id: 'orderSource', class: OrderDataSource::class, method: 'getOrder', args: ['#orderId'])
])]
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
use Kassko\DataMapper\Attribute\MultiPropDataSource;
use Kassko\DataMapper\Attribute\DataSourceRef;
use Kassko\DataMapper\Attribute\DataSourcesStore;

#[DataSourcesStore([
    new MultiPropDataSource(id: 'sourceA', class: SourceA::class, method: 'getData'),
    new MultiPropDataSource(id: 'sourceB', class: SourceB::class, method: 'getData'),
    new MultiPropDataSource(id: 'sourceC', class: SourceC::class, method: 'process', args: ['#propA'])
])]
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
        exceptionOnNoValidFallback: NoValidDataSourceException::class,
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

#[PropertyConfigStore([
    new PropertyConfig(id: 'premium', class: PremiumOffice::class),
    new PropertyConfig(id: 'regional', class: RegionalOffice::class),
    new PropertyConfig(id: 'default', class: Office::class),
])]
class Chief
{
    // Access parent context in expressions
    #[Property(
        configCandidates: [
            ['id' => 'premium', 'when' => "expr(context('tier') === 'premium')"],
            ['id' => 'regional', 'when' => "expr(contextKeyExists('region'))"],
        ],
        defaultConfigCandidate: 'default'
    )]
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

## Parameter Injection with Param Attribute

### Constructor Parameter Injection

Use `#[Param]` to inject values into constructor parameters. All constructor parameters MUST have a Param attribute:

```php
use Kassko\DataMapper\Attribute\Param;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

class User
{
    use LoadableTrait;
    
    private string $userId;
    
    public function __construct(
        #[Param(value: "expr(context('requestedUserId'))")]
        string $userId
    ) {
        $this->userId = $userId;
    }
}

// Set context before instantiation
ContextRegistry::set('requestedUserId', '12345');

// Instantiate via Loader
$loader = LoaderRegistry::get();
$user = $loader->instantiateWithParams(User::class);
echo $user->userId; // "12345"
```

### Getter Parameter Injection

Inject services or context values into getter parameters:

```php
use Kassko\DataMapper\Attribute\Param;

class Person
{
    use LoadableTrait;
    
    private ?string $name = null;
    
    public function getName(
        #[Param(value: "expr(service('nameFormatter'))")]
        $formatter = null
    ): ?string {
        $this->loadProperty('name');
        return $formatter?->format($this->name) ?? $this->name;
    }
}
```

### Setter Parameter Injection

The first setter parameter receives the hydrated value (no Param). Additional parameters can have Param:

```php
use Kassko\DataMapper\Attribute\Param;

class Person
{
    private ?string $email = null;
    
    public function setEmail(
        $email,  // First parameter: value from hydration, NO Param
        #[Param(value: "expr(context('emailDomain'))")]
        $domain = null
    ): void {
        $this->email = $domain ? $email . '@' . $domain : $email;
    }
}
```

### Supported Param Values

| Value Type | Example |
|------------|---------|
| Static | `"fixed value"` |
| Context | `"expr(context('key'))"` |
| Service | `"expr(service('serviceId'))"` |
| Source | `"expr(source('sourceId'))"` |
| Property* | `"#propertyName"` or `"expr(property('name'))"` |
| Object* | `"##object"` |

\* Property references are forbidden in constructor parameters.

See [Param Attribute Documentation](docs/attributes/Param.md) for more details.
## Attribute Cascading

DataMapper supports cascading PHP 8 attributes from parent classes and traits.

### DataSourcesStore Inheritance

```php
use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\Attribute\DataSourceRef;
use Kassko\DataMapper\Attribute\SinglePropDataSource;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

// Parent class defines data sources
#[DataSourcesStore([
    new SinglePropDataSource(id: 'parentSource', class: ParentDataSource::class, method: 'getData'),
    new SinglePropDataSource(id: 'sharedSource', class: ParentDataSource::class, method: 'getShared'),
])]
abstract class BaseEntity
{
    private ?int $id = null;
}

// Trait defines additional data sources
#[DataSourcesStore([
    new SinglePropDataSource(id: 'traitSource', class: TraitDataSource::class, method: 'getData'),
])]
trait DataSourceTrait {}

// Child class inherits from parent and trait, can add its own
#[DataSourcesStore([
    new SinglePropDataSource(id: 'childSource', class: ChildDataSource::class, method: 'getData'),
    new SinglePropDataSource(id: 'sharedSource', class: ChildDataSource::class, method: 'getShared'), // Overrides parent
])]
class ChildEntity extends BaseEntity
{
    use LoadableTrait;
    use DataSourceTrait;
    
    #[DataSourceRef(id: 'parentSource')] // Reference parent's source - works!
    private ?string $fromParent = null;
    
    #[DataSourceRef(id: 'traitSource')] // Reference trait's source - works!
    private ?string $fromTrait = null;
    
    #[DataSourceRef(id: 'childSource')] // Reference own source
    private ?string $fromChild = null;
    
    #[DataSourceRef(id: 'sharedSource')] // Uses child's override, not parent's
    private ?string $sharedValue = null;
    
    public function getFromParent(): ?string
    {
        $this->loadProperty('fromParent');
        return $this->fromParent;
    }
}
```

### PropertyConfigStore Inheritance

```php
use Kassko\DataMapper\Attribute\Property;
use Kassko\DataMapper\Attribute\PropertyConfig;
use Kassko\DataMapper\Attribute\PropertyConfigStore;

// Parent defines configs
#[PropertyConfigStore([
    new PropertyConfig(id: 'parentConfig', class: ParentProduct::class),
])]
abstract class BaseContainer {}

// Trait defines additional configs
#[PropertyConfigStore([
    new PropertyConfig(id: 'traitConfig', class: TraitProduct::class),
])]
trait ConfigTrait {}

// Child can reference configs from parent, trait, and self
#[PropertyConfigStore([
    new PropertyConfig(id: 'childConfig', class: ChildProduct::class),
])]
class ChildContainer extends BaseContainer
{
    use ConfigTrait;
    
    #[Property(
        configCandidates: [
            ['id' => 'childConfig', 'when' => "expr(rawDataItemExists('childType'))"],
            ['id' => 'parentConfig', 'when' => "expr(rawDataItemExists('parentType'))"], // Works!
            ['id' => 'traitConfig', 'when' => "expr(rawDataItemExists('traitType'))"], // Works!
        ],
        defaultConfigCandidate: 'childConfig'
    )]
    private ?object $item = null;
}
```

### Conflict Resolution

When child and parent/trait define the same ID:

- **Child wins**: The child's definition takes precedence
- **Logged**: A warning is logged for debugging

See [Attribute Cascading Documentation](docs/concepts/AttributeCascading.md) for more details.

## ObjectMapper - DTO Mapping

The ObjectMapper allows you to map domain objects from DTO (Data Transfer Object) sources instead of raw data arrays.

### Basic DTO Mapping

```php
use Kassko\DataMapper\DataMapperBuilder;
use Kassko\DataMapper\Attribute\Property;

// Source DTO
class PersonDto
{
    public function __construct(
        public string $firstName = '',
        public string $lastName = '',
        public ?string $email = null
    ) {}
}

// Domain object
class Person
{
    #[Property(sourceField: 'firstName')]
    private ?string $firstName = null;
    
    #[Property(sourceField: 'lastName')]
    private ?string $lastName = null;
    
    private ?string $email = null;
    
    // Getters...
}

// Usage
$dataMapper = (new DataMapperBuilder())->build();
$objectMapper = $dataMapper->getObjectMapper();

$dto = new PersonDto('John', 'Doe', 'john@example.com');
$person = $objectMapper->map(Person::class, $dto);

echo $person->getFirstName(); // "John"
```

### Deep Path Mapping

Map properties from nested objects in the DTO:

```php
// Nested DTOs
class AddressDto
{
    public function __construct(
        public string $street = '',
        public string $city = '',
        public string $postalCode = ''
    ) {}
}

class PersonDto
{
    public function __construct(
        public string $firstName = '',
        public string $lastName = '',
        public ?AddressDto $address = null
    ) {}
}

// Domain object with deep path mapping
class Person
{
    #[Property(sourceField: 'firstName')]
    private ?string $firstName = null;
    
    #[Property(sourceField: 'lastName')]
    private ?string $lastName = null;
    
    // Deep path: maps from $dto->address->street
    #[Property(sourceField: 'address.street')]
    private ?string $street = null;
    
    // Deep path: maps from $dto->address->city
    #[Property(sourceField: 'address.city')]
    private ?string $city = null;
    
    // Getters...
}

// Usage
$addressDto = new AddressDto('123 Main St', 'New York', '10001');
$personDto = new PersonDto('John', 'Doe', $addressDto);

$person = $objectMapper->map(Person::class, $personDto);

echo $person->getStreet(); // "123 Main St"
echo $person->getCity();   // "New York"
```

### Custom Object Mappers

Register custom object mappers for complex transformations:

```php
use Kassko\DataMapper\DataMapperBuilder;
use Kassko\DataMapper\Attribute\CustomObjectMapper;

// Register custom mapper
$builder = new DataMapperBuilder();
$builder->addCustomObjectMapper('money_mapper', function(object $source): Money {
    return new Money(
        amount: $source->amount,
        currency: Currency::from($source->currencyCode)
    );
});

$dataMapper = $builder->build();

// Use in a domain object
class Order
{
    #[CustomObjectMapper(
        key: 'money_mapper',
        inputClass: MoneyDto::class,
        outputClass: Money::class
    )]
    private ?Money $total = null;
    
    public function getTotal(): ?Money
    {
        return $this->total;
    }
}

// Retrieve mapper programmatically
$moneyMapper = $dataMapper->getCustomObjectMapper('money_mapper');
$money = $moneyMapper($moneyDto);
```

### Hydrator vs ObjectMapper

| Feature | Hydrator | ObjectMapper |
|---------|----------|--------------|
| Input | Array (raw data) | Object (DTO) |
| Method | `hydrate()` | `map()` |
| Use case | API responses, JSON | DTO transformations |

```php
$dataMapper = (new DataMapperBuilder())->build();

// Use Hydrator for array data
$hydrator = $dataMapper->getHydrator();
$person1 = $hydrator->hydrate(Person::class, ['first_name' => 'John']);

// Use ObjectMapper for DTO sources
$objectMapper = $dataMapper->getObjectMapper();
$person2 = $objectMapper->map(Person::class, $personDto);
```