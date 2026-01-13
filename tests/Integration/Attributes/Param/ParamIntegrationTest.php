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

namespace Kassko\DataMapper\Tests\Integration\Attributes\Param;

use Kassko\DataMapper\ArrayServiceLocator;
use Kassko\DataMapper\DataMapper;
use Kassko\DataMapper\DataMapperBuilder;
use Kassko\DataMapper\Registry\ContextRegistry;
use Kassko\DataMapper\Registry\LoaderRegistry;
use Kassko\DataMapper\ServiceResolver;
use Kassko\DataMapper\Tests\Integration\Attributes\Param\Fixtures\Address;
use Kassko\DataMapper\Tests\Integration\Attributes\Param\Fixtures\NestedPersonWithParam;
use Kassko\DataMapper\Tests\Integration\Attributes\Param\Fixtures\ParentWithNestedParam;
use Kassko\DataMapper\Tests\Integration\Attributes\Param\Fixtures\PersonWithConstructorParam;
use Kassko\DataMapper\Tests\Integration\Attributes\Param\Fixtures\PersonWithIndexedAdder;
use Kassko\DataMapper\Tests\Integration\Attributes\Param\Fixtures\PersonWithOptionalConstructorParam;
use Kassko\DataMapper\Tests\Integration\Attributes\Param\Fixtures\PersonWithPropertyExprParam;
use Kassko\DataMapper\Tests\Integration\Attributes\Param\Fixtures\PersonWithPropertyRefParam;
use Kassko\DataMapper\Tests\Integration\Attributes\Param\Fixtures\PersonWithServiceParam;
use Kassko\DataMapper\Tests\Integration\Attributes\Param\Fixtures\PersonWithStaticParam;
use Kassko\DataMapper\Tests\Integration\Attributes\Param\Fixtures\PersonWithoutParam;
use PHPUnit\Framework\TestCase;

class ParamIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        ContextRegistry::clear();
        LoaderRegistry::clear();
    }

    protected function tearDown(): void
    {
        ContextRegistry::clear();
        LoaderRegistry::clear();
    }

    // ========================================================================
    // Constructor Parameter Tests
    // ========================================================================

    public function testConstructorWithContextParam(): void
    {
        // Set up context
        ContextRegistry::set('defaultName', 'John Doe');
        
        $serviceResolver = new ServiceResolver();
        $dataMapper = new DataMapper($serviceResolver);
        $loader = LoaderRegistry::get();
        
        // Instantiate class with Param-annotated constructor
        $object = $loader->instantiateWithParams(PersonWithConstructorParam::class);
        
        $this->assertEquals('John Doe', $object->name);
    }

    public function testConstructorWithStaticParam(): void
    {
        $serviceResolver = new ServiceResolver();
        $dataMapper = new DataMapper($serviceResolver);
        $loader = LoaderRegistry::get();
        
        $object = $loader->instantiateWithParams(PersonWithStaticParam::class);
        
        $this->assertEquals('Default Static Name', $object->name);
    }

    public function testConstructorWithServiceParam(): void
    {
        $nameGenerator = new class {
            public function generate(): string
            {
                return 'Generated Name';
            }
        };
        
        $serviceLocator = new ArrayServiceLocator([
            'nameGenerator' => $nameGenerator,
        ]);
        
        $builder = new DataMapperBuilder();
        $builder->addServiceLocator($serviceLocator);
        $builder->build();
        $loader = LoaderRegistry::get();
        
        $object = $loader->instantiateWithParams(PersonWithServiceParam::class);
        
        // The service is injected, actual usage would be via the object
        $this->assertNotNull($object->service);
    }

    public function testConstructorWithoutParamThrowsException(): void
    {
        $serviceResolver = new ServiceResolver();
        $dataMapper = new DataMapper($serviceResolver);
        $loader = LoaderRegistry::get();
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('must either be optional or have a #[Param] attribute');
        
        $loader->instantiateWithParams(PersonWithoutParam::class);
    }

    public function testConstructorWithPropertyReferenceThrowsException(): void
    {
        $serviceResolver = new ServiceResolver();
        $dataMapper = new DataMapper($serviceResolver);
        $loader = LoaderRegistry::get();
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('forbidden property reference');
        
        $loader->instantiateWithParams(PersonWithPropertyRefParam::class);
    }

    public function testConstructorWithPropertyExpressionThrowsException(): void
    {
        $serviceResolver = new ServiceResolver();
        $dataMapper = new DataMapper($serviceResolver);
        $loader = LoaderRegistry::get();
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("forbidden property() expression");
        
        $loader->instantiateWithParams(PersonWithPropertyExprParam::class);
    }

    public function testNestedObjectInstantiationWithParam(): void
    {
        // Set up context for nested object construction
        ContextRegistry::set('nestedName', 'Nested Person');
        
        $dataSource = new class {
            public function getData(): array
            {
                return [
                    'title' => 'Parent',
                    'child' => ['age' => 30],
                ];
            }
        };

        $serviceLocator = new ArrayServiceLocator([
            'parentSource' => $dataSource,
        ]);

        $builder = new DataMapperBuilder();
        $builder->addServiceLocator($serviceLocator);
        $builder->build();

        $parent = new ParentWithNestedParam();
        
        // Trigger lazy loading
        $title = $parent->getTitle();
        $child = $parent->getChild();
        
        $this->assertEquals('Parent', $title);
        $this->assertNotNull($child);
        // Nested object should have constructor param resolved
        $this->assertEquals('Nested Person', $child->name);
    }

    // ========================================================================
    // Optional Constructor Parameter Tests (New Feature)
    // ========================================================================

    public function testConstructorWithOptionalParameterWithoutParam(): void
    {
        // Set up context for the Param-annotated parameter
        ContextRegistry::set('userId', 'user-123');
        
        $serviceResolver = new ServiceResolver();
        $dataMapper = new DataMapper($serviceResolver);
        $loader = LoaderRegistry::get();
        
        // Instantiate class with optional constructor parameter (no #[Param] required)
        $object = $loader->instantiateWithParams(PersonWithOptionalConstructorParam::class);
        
        // Optional parameter should use default value (null)
        $this->assertNull($object->socialSecurityNumber);
        // Param-annotated parameter should be resolved from context
        $this->assertEquals('user-123', $object->id);
    }

    // ========================================================================
    // Indexed Adder with Param Tests (New Feature)
    // ========================================================================

    public function testIndexedAdderWithParamInThirdPosition(): void
    {
        // Set up context for the Param in indexed adder
        ContextRegistry::set('address_prefix', 'addr_');
        
        $serviceResolver = new ServiceResolver();
        $dataMapper = new DataMapper($serviceResolver);
        $loader = LoaderRegistry::get();
        
        $person = new PersonWithIndexedAdder();
        
        // Hydrate with simple address data (strings)
        $data = [
            'addresses' => [
                'home' => '123 Main St, Paris',
                'work' => '456 Office Blvd, Lyon',
            ],
        ];
        
        // Use the loader's hydrateObject method
        $reflection = new \ReflectionClass($loader);
        $method = $reflection->getMethod('hydrateObject');
        $method->invoke($loader, $person, $data, null, 0);
        
        $addresses = $person->getAddresses();
        $methodCalls = $person->getMethodCalls();
        
        // The addresses should be stored with prefix applied to keys
        $this->assertCount(2, $addresses);
        $this->assertArrayHasKey('addr_home', $addresses);
        $this->assertArrayHasKey('addr_work', $addresses);
        
        // Verify the Param was resolved correctly
        $this->assertEquals('addr_', $methodCalls[0]['prefix']);
        $this->assertEquals('addr_', $methodCalls[1]['prefix']);
    }
}
