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

use Kassko\DataMapper\ArrayServiceLocator;
use Kassko\DataMapper\Attribute\DataSourceRef;
use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\Attribute\MultiPropDataSource;
use Kassko\DataMapper\Attribute\Param;
use Kassko\DataMapper\Attribute\Property;
use Kassko\DataMapper\DataMapper;
use Kassko\DataMapper\DataMapperBuilder;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;
use Kassko\DataMapper\Registry\ContextRegistry;
use Kassko\DataMapper\Registry\LoaderRegistry;
use Kassko\DataMapper\ServiceResolver;
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
        $this->expectExceptionMessage('must have a #[Param] attribute');
        
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
}

// Test fixtures

class PersonWithConstructorParam
{
    public string $name;
    
    public function __construct(
        #[Param(value: "expr(context('defaultName'))")]
        string $name
    ) {
        $this->name = $name;
    }
}

class PersonWithStaticParam
{
    public string $name;
    
    public function __construct(
        #[Param(value: "Default Static Name")]
        string $name
    ) {
        $this->name = $name;
    }
}

class PersonWithServiceParam
{
    public $service;
    
    public function __construct(
        #[Param(value: "expr(service('nameGenerator'))")]
        $service
    ) {
        $this->service = $service;
    }
}

class PersonWithoutParam
{
    public string $name;
    
    public function __construct(string $name)
    {
        $this->name = $name;
    }
}

class PersonWithPropertyRefParam
{
    public string $name;
    
    public function __construct(
        #[Param(value: "#name")]
        string $name
    ) {
        $this->name = $name;
    }
}

class PersonWithPropertyExprParam
{
    public string $name;
    
    public function __construct(
        #[Param(value: "expr(property('name'))")]
        string $name
    ) {
        $this->name = $name;
    }
}

class NestedPersonWithParam
{
    public string $name;
    public int $age;
    
    public function __construct(
        #[Param(value: "expr(context('nestedName'))")]
        string $name
    ) {
        $this->name = $name;
    }
}

#[DataSourcesStore([
    new MultiPropDataSource(id: 'parentSource', class: 'parentSource', method: 'getData'),
])]
class ParentWithNestedParam
{
    use LoadableTrait;
    
    #[DataSourceRef(id: 'parentSource')]
    private ?string $title = null;
    
    #[Property(class: NestedPersonWithParam::class)]
    #[DataSourceRef(id: 'parentSource')]
    private ?NestedPersonWithParam $child = null;
    
    public function getTitle(): ?string
    {
        $this->loadProperty('title');
        return $this->title;
    }
    
    public function getChild(): ?NestedPersonWithParam
    {
        $this->loadProperty('child');
        return $this->child;
    }
}
