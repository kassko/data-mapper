<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Tests\Integration;

use Kassko\DataMapper\ArrayServiceLocator;
use Kassko\DataMapper\Attribute\MultiPropDataSource;
use Kassko\DataMapper\Attribute\DataSourceRef;
use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\DataMapperBuilder;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;
use Kassko\DataMapper\Registry\LazyLoaderRegistry;
use PHPUnit\Framework\TestCase;

class PropertyLockingTest extends TestCase
{
    protected function tearDown(): void
    {
        LazyLoaderRegistry::clear();
    }

    public function testLockedPropertyIsNotHydrated(): void
    {
        // Track what gets loaded
        $loadedCount = 0;
        
        // Create a data source
        $dataSource = new class($loadedCount) {
            private int $loadCount = 0;
            
            public function __construct(int $initialCount)
            {
                $this->loadCount = $initialCount;
            }
            
            public function getData(): array
            {
                $this->loadCount++;
                return ['value' => 'from-datasource'];
            }
            
            public function getLoadCount(): int
            {
                return $this->loadCount;
            }
        };
        
        // Setup service locator
        $serviceLocator = new ArrayServiceLocator([
            'TestSource' => $dataSource,
        ]);
        
        $builder = new DataMapperBuilder();
        $builder->addLocator($serviceLocator);
        $builder->build();
        
        // Create test object
        $testObject = new 
        #[DataSourcesStore([
            new MultiPropDataSource(id: 'source', class: 'TestSource', method: 'getData'),
        ])]
        class {
            use LoadableTrait;
            
            #[DataSourceRef(id: 'source')]
            private ?string $value = null;
            
            public function setValue(string $val): void
            {
                $this->value = $val;
                $this->lockProperty('value');
            }
            
            public function getValue(): ?string
            {
                $this->loadProperty('value');
                return $this->value;
            }
            
            public function unlockValue(): void
            {
                $this->unlockProperty('value');
            }
        };
        
        // Set value manually and lock it
        $testObject->setValue('manual-value');
        
        // Try to load the property - should be blocked
        $value = $testObject->getValue();
        
        // Verify the manual value was NOT overwritten
        $this->assertSame('manual-value', $value);
        $this->assertSame(0, $dataSource->getLoadCount(), 'DataSource should not have been called');
        
        // Unlock and load again
        $testObject->unlockValue();
        $value = $testObject->getValue();
        
        // Now it should load from data source
        $this->assertSame('from-datasource', $value);
        $this->assertSame(1, $dataSource->getLoadCount(), 'DataSource should have been called once');
    }

    public function testPropertyLockInChildClass(): void
    {
        // Create a data source
        $dataSource = new class {
            public function getData(): array
            {
                return ['firstName' => 'John', 'lastName' => 'Doe'];
            }
        };
        
        // Setup service locator
        $serviceLocator = new ArrayServiceLocator([
            'PersonSource' => $dataSource,
        ]);
        
        $builder = new DataMapperBuilder();
        $builder->addLocator($serviceLocator);
        $builder->build();
        
        // Create test object that can lock properties
        $testObject = new 
        #[DataSourcesStore([
            new MultiPropDataSource(id: 'personData', class: 'PersonSource', method: 'getData'),
        ])]
        class {
            use LoadableTrait;
            
            #[DataSourceRef(id: 'personData')]
            protected ?string $firstName = null;
            
            #[DataSourceRef(id: 'personData')]
            protected ?string $lastName = null;
            
            public function setFirstName(string $value): void
            {
                $this->firstName = $value;
                $this->lockProperty('firstName');
            }
            
            public function getFirstName(): ?string
            {
                $this->loadProperty('firstName');
                return $this->firstName;
            }
            
            public function getLastName(): ?string
            {
                $this->loadProperty('lastName');
                return $this->lastName;
            }
            
            public function freezeAllData(): void
            {
                $this->lockProperty('firstName');
                $this->lockProperty('lastName');
            }
        };
        
        // Set firstName manually
        $testObject->setFirstName('Manual');
        
        // Try to load - firstName should be locked, lastName should load
        $firstName = $testObject->getFirstName();
        $lastName = $testObject->getLastName();
        
        $this->assertSame('Manual', $firstName);
        $this->assertSame('Doe', $lastName);
        
        // Now freeze all data in a fresh instance
        $testObject2 = new 
        #[DataSourcesStore([
            new MultiPropDataSource(id: 'personData', class: 'PersonSource', method: 'getData'),
        ])]
        class {
            use LoadableTrait;
            
            #[DataSourceRef(id: 'personData')]
            protected ?string $firstName = null;
            
            #[DataSourceRef(id: 'personData')]
            protected ?string $lastName = null;
            
            public function getFirstName(): ?string
            {
                $this->loadProperty('firstName');
                return $this->firstName;
            }
            
            public function getLastName(): ?string
            {
                $this->loadProperty('lastName');
                return $this->lastName;
            }
            
            public function freezeAllData(): void
            {
                $this->lockProperty('firstName');
                $this->lockProperty('lastName');
            }
        };
        
        $testObject2->freezeAllData();
        
        // Both should remain null since they're locked before loading
        $firstName2 = $testObject2->getFirstName();
        $lastName2 = $testObject2->getLastName();
        
        $this->assertNull($firstName2);
        $this->assertNull($lastName2);
    }

    public function testIsPropertyLockedMethod(): void
    {
        $testObject = new class {
            use LoadableTrait;
            
            private ?string $prop = null;
            
            public function testLocking(): bool
            {
                // Initially not locked
                $before = $this->isPropertyLocked('prop');
                
                // Lock it
                $this->lockProperty('prop');
                $during = $this->isPropertyLocked('prop');
                
                // Unlock it
                $this->unlockProperty('prop');
                $after = $this->isPropertyLocked('prop');
                
                return !$before && $during && !$after;
            }
        };
        
        $this->assertTrue($testObject->testLocking());
    }
}
