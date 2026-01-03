# Integration Tests Guide

This guide provides detailed information about integration tests in the Data Mapper library.

## Purpose

Integration tests verify that:

- PHP 8 attributes are correctly parsed and processed
- Lazy loading works with various data source configurations
- Service resolution functions properly
- Hydration produces expected results
- Object lifecycle hooks are called correctly

## Test Organization

### Attribute Categories

| Category | Description |
|----------|-------------|
| `Context` | Context management and injection |
| `CustomHydrator` | Custom hydration logic |
| `DataSource` | Single property data sources (alias of SinglePropDataSource) |
| `DataSourceStore` | Data source storage and retrieval |
| `Hook` | Legacy hook attribute |
| `MultiPropDataSource` | Multi-property data sources |
| `PropertyHydratingHook` | Hooks during property hydration |
| `PropertyInstantiatingHook` | Hooks after object instantiation |
| `SinglePropDataSource` | Single property data sources |

### Sub-Topic Tests

Some attributes have multiple test scenarios organized as sub-topics:

```
DataSourceCheckChain/           # Data source chaining
DataSourceCheckLoadingScope/    # Loading scope behavior
DataSourceRefCheckCandidates/   # Candidate selection
DataSourceRefCheckProviders/    # Provider resolution
PropertyCheckExpandNoExpand/    # Property expansion
PropertyCheckInstanceMapping/   # Instance-based mapping
PropertyCheckMapping/           # Basic property mapping
```

## Writing Integration Tests

### Standard Test Structure

```php
<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Tests\Integration\Attributes\MyAttribute;

use Kassko\DataMapper\DataMapper;
use Kassko\DataMapper\Registry\LoaderRegistry;
use Kassko\DataMapper\ServiceResolver;
use Kassko\DataMapper\Tests\TestHelpers\LocalFixtureAutoloadTrait;
use Kassko\Sample\MyAttribute\MyEntity;
use PHPUnit\Framework\TestCase;

class MyAttributeTest extends TestCase
{
    use LocalFixtureAutoloadTrait;

    protected function tearDown(): void
    {
        LoaderRegistry::clear();
    }

    public function testBasicFunctionality(): void
    {
        new DataMapper(new ServiceResolver());
        
        $entity = new MyEntity(1);
        
        $this->assertEquals('expected', $entity->getValue());
    }
}
```

### Fixture Structure

```php
<?php

declare(strict_types=1);

namespace Kassko\Sample\MyAttribute;

use Kassko\DataMapper\Attribute\SinglePropDataSource;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

class MyEntity
{
    use LoadableTrait;

    private int $id;

    #[SinglePropDataSource(class: MyDataSource::class, method: 'getValue', args: ['#id'])]
    private ?string $value = null;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function getValue(): ?string
    {
        $this->loadProperty('value');
        return $this->value;
    }
}
```

### Data Source Fixtures

```php
<?php

declare(strict_types=1);

namespace Kassko\Sample\MyAttribute;

class MyDataSource
{
    public function getValue(int $id): ?string
    {
        return match ($id) {
            1 => 'Value One',
            2 => 'Value Two',
            default => null,
        };
    }
}
```

## Test Scenarios

### Basic Loading

Test that properties are loaded correctly:

```php
public function testPropertyIsLoaded(): void
{
    new DataMapper(new ServiceResolver());
    
    $entity = new Entity(1);
    $value = $entity->getValue();
    
    $this->assertEquals('expected value', $value);
}
```

### Multiple Properties

Test independent loading of multiple properties:

```php
public function testMultiplePropertiesLoadIndependently(): void
{
    new DataMapper(new ServiceResolver());
    
    $entity = new Entity(1);
    
    $this->assertEquals('name', $entity->getName());
    $this->assertEquals('email', $entity->getEmail());
}
```

### Different IDs

Test behavior with different entity IDs:

```php
public function testDifferentIds(): void
{
    new DataMapper(new ServiceResolver());
    
    $entity1 = new Entity(1);
    $entity2 = new Entity(2);
    
    $this->assertEquals('Value One', $entity1->getValue());
    $this->assertEquals('Value Two', $entity2->getValue());
}
```

### Unknown IDs

Test behavior with non-existent IDs:

```php
public function testUnknownIdReturnsNull(): void
{
    new DataMapper(new ServiceResolver());
    
    $entity = new Entity(999);
    
    $this->assertNull($entity->getValue());
}
```

### Caching

Test that loaded values are cached:

```php
public function testPropertyLoadingIsCached(): void
{
    new DataMapper(new ServiceResolver());
    
    $entity = new Entity(1);
    
    $value1 = $entity->getValue();
    $value2 = $entity->getValue();
    
    $this->assertSame($value1, $value2);
}
```

## Hook Tests

### Instantiation Hooks

```php
public function testInstantiationHookIsCalled(): void
{
    new DataMapper(new ServiceResolver());
    
    $entity = new TrackedEntity(1);
    $entity->onInstantiated($entity);
    
    $this->assertTrue($entity->isInstantiationTracked());
}
```

### External Service Hooks

```php
public function testExternalServiceHook(): void
{
    new DataMapper(new ServiceResolver());
    
    $tracker = new InstantiationTracker();
    $entity = new EntityWithExternalHook(1);
    $tracker->trackInstantiation($entity);
    
    $this->assertEquals(1, InstantiationTracker::getTrackedCount());
}
```

## Common Patterns

### Static Data Sources

For simple test data, use static arrays:

```php
class ProductDataSource
{
    private static array $products = [
        1 => ['name' => 'Laptop', 'price' => 999.99],
        2 => ['name' => 'Mouse', 'price' => 29.99],
    ];

    public function getName(int $id): ?string
    {
        return self::$products[$id]['name'] ?? null;
    }
}
```

### Resettable Trackers

For tracking hook calls, use static state with reset:

```php
class InstantiationTracker
{
    private static array $tracked = [];

    public function track(object $entity): void
    {
        self::$tracked[] = $entity;
    }

    public static function reset(): void
    {
        self::$tracked = [];
    }
}
```

## Debugging Tips

1. **Check fixture namespace**: Ensure fixtures use `Kassko\Sample\<AttributeName>\`
2. **Verify LoadableTrait**: Entity must use `LoadableTrait`
3. **Check method calls**: `loadProperty()` must be called in getters
4. **Inspect data source return type**: Match return type to property type
5. **Clear registry**: Always clear `LoaderRegistry` between tests
