<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Tests\Integration;

use Kassko\DataMapper\Attribute\DataSource;
use Kassko\DataMapper\Attribute\DataSourceRef;
use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\Attribute\Property;
use Kassko\DataMapper\DataMapper;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;
use Kassko\DataMapper\Registry\LazyLoaderRegistry;
use Kassko\Sample\AggregationDataSource;
use PHPUnit\Framework\TestCase;

class DataSourceAggregationTest extends TestCase
{
    protected function tearDown(): void
    {
        LazyLoaderRegistry::clear();
    }

    public function testAggregationMergesResults(): void
    {
        new DataMapper();
        
        $object = new #[DataSourcesStore([
            new DataSource(id: 'providerA', class: AggregationDataSource::class, method: 'providerA', supplySeveralProperties: true),
            new DataSource(id: 'providerB', class: AggregationDataSource::class, method: 'providerB', supplySeveralProperties: true),
            new DataSource(id: 'providerC', class: AggregationDataSource::class, method: 'providerC', supplySeveralProperties: true),
        ])] class {
            use LoadableTrait;
            
            #[DataSourceRef(providers: ['providerA', 'providerB', 'providerC'])]
            #[Property(name: 'name')]
            private ?string $name = null;
            
            #[DataSourceRef(providers: ['providerA', 'providerB', 'providerC'])]
            #[Property(name: 'age')]
            private ?int $age = null;
            
            #[DataSourceRef(providers: ['providerA', 'providerB', 'providerC'])]
            #[Property(name: 'city')]
            private ?string $city = null;
            
            public function getName(): ?string
            {
                $this->loadProperty('name');
                return $this->name;
            }
            
            public function getAge(): ?int
            {
                $this->loadProperty('age');
                return $this->age;
            }
            
            public function getCity(): ?string
            {
                $this->loadProperty('city');
                return $this->city;
            }
        };
        
        // Last provider wins for conflicting keys
        $this->assertEquals('Alice', $object->getName());
        $this->assertEquals(30, $object->getAge()); // providerB overwrites providerA
        $this->assertEquals('London', $object->getCity()); // providerC overwrites providerB
    }
    
    public function testAggregationWithNestedArrays(): void
    {
        new DataMapper();
        
        $object = new #[DataSourcesStore([
            new DataSource(id: 'providerA', class: AggregationDataSource::class, method: 'providerNestedA', supplySeveralProperties: true),
            new DataSource(id: 'providerB', class: AggregationDataSource::class, method: 'providerNestedB', supplySeveralProperties: true),
        ])] class {
            use LoadableTrait;
            
            #[DataSourceRef(providers: ['providerA', 'providerB'])]
            #[Property(name: 'config')]
            private ?array $config = null;
            
            public function getConfig(): ?array
            {
                $this->loadProperty('config');
                return $this->config;
            }
        };
        
        $config = $object->getConfig();
        
        // array_replace_recursive merges nested arrays
        $this->assertEquals('localhost', $config['db']['host']);
        $this->assertEquals(5432, $config['db']['port']); // providerB overwrites
        $this->assertEquals('admin', $config['db']['user']); // Added by providerB
    }
}
