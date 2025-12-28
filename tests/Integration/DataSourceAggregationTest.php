<?php

declare(strict_types=1);

/*
 * This file is part of Data Mapper.
 *
 * Copyright 2025 kassko 
 *
 * For the full copyright and license information,
 * please view the LICENSE and NOTICE files that were distributed with this source code.
 */

namespace Kassko\DataMapper\Tests\Integration;

use Kassko\DataMapper\Attribute\SinglePropDataSource;
use Kassko\DataMapper\Attribute\DataSourceRef;
use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\Attribute\Property;
use Kassko\DataMapper\DataMapper;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;
use Kassko\DataMapper\Registry\LoaderRegistry;
use Kassko\Sample\AggregationDataSource;
use PHPUnit\Framework\TestCase;

class DataSourceAggregationTest extends TestCase
{
    protected function tearDown(): void
    {
        LoaderRegistry::clear();
    }

    public function testAggregationMergesResults(): void
    {
        new DataMapper(new \Kassko\DataMapper\ServiceResolver());
        
        $object = new #[DataSourcesStore([
            new SinglePropDataSource(id: 'providerA', class: AggregationDataSource::class, method: 'providerA'),
            new SinglePropDataSource(id: 'providerB', class: AggregationDataSource::class, method: 'providerB'),
            new SinglePropDataSource(id: 'providerC', class: AggregationDataSource::class, method: 'providerC'),
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
        new DataMapper(new \Kassko\DataMapper\ServiceResolver());
        
        $object = new #[DataSourcesStore([
            new SinglePropDataSource(id: 'providerA', class: AggregationDataSource::class, method: 'providerNestedA'),
            new SinglePropDataSource(id: 'providerB', class: AggregationDataSource::class, method: 'providerNestedB'),
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
