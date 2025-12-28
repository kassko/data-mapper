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
use Kassko\DataMapper\Attribute\PropertyHydratingHook;
use Kassko\DataMapper\DataMapper;
use Kassko\DataMapper\DataMapperBuilder;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;
use Kassko\DataMapper\Registry\ContextRegistry;
use Kassko\DataMapper\Registry\LoaderRegistry;
use Kassko\DataMapper\ServiceResolver;
use PHPUnit\Framework\TestCase;

class PropertyHydratingHookTest extends TestCase
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

    public function testBeforeHydrateHookIsCalled(): void
    {
        $dataSource = new class {
            public function getData(): array
            {
                return ['firstName' => 'John', 'lastName' => 'Doe'];
            }
        };

        $serviceLocator = new ArrayServiceLocator([
            'personSource' => $dataSource,
        ]);

        $builder = new DataMapperBuilder();
        $builder->addLocator($serviceLocator);
        $builder->build();

        $person = new PersonWithBeforeHook();
        $person->getFirstName();

        $this->assertTrue($person->beforeHookCalled);
        $this->assertNotNull($person->rawDataReceived);
        $this->assertEquals(['firstName' => 'John', 'lastName' => 'Doe'], $person->rawDataReceived);
    }

    public function testAfterHydrateHookIsCalled(): void
    {
        $dataSource = new class {
            public function getData(): array
            {
                return ['firstName' => 'Jane', 'lastName' => 'Smith'];
            }
        };

        $serviceLocator = new ArrayServiceLocator([
            'personSource' => $dataSource,
        ]);

        $builder = new DataMapperBuilder();
        $builder->addLocator($serviceLocator);
        $builder->build();

        $person = new PersonWithAfterHook();
        $person->getFirstName();

        $this->assertTrue($person->afterHookCalled);
        $this->assertEquals('Jane Smith', $person->fullName);
    }

    public function testBeforeAndAfterHooksExecuteInOrder(): void
    {
        $dataSource = new class {
            public function getData(): array
            {
                return ['value' => 'original'];
            }
        };

        $serviceLocator = new ArrayServiceLocator([
            'orderSource' => $dataSource,
        ]);

        $builder = new DataMapperBuilder();
        $builder->addLocator($serviceLocator);
        $builder->build();

        $obj = new ObjectWithBothHooks();
        $obj->getValue();

        $this->assertEquals(['before', 'after'], $obj->executionOrder);
    }

    public function testExternalServiceHook(): void
    {
        $hookService = new class {
            public bool $called = false;
            public array $receivedData = [];
            
            public function onBeforeHydrate(array $rawData): void
            {
                $this->called = true;
                $this->receivedData = $rawData;
            }
        };

        $dataSource = new class {
            public function getData(): array
            {
                return ['name' => 'Test'];
            }
        };

        $serviceLocator = new ArrayServiceLocator([
            'externalHookService' => $hookService,
            'dataSource' => $dataSource,
        ]);

        $builder = new DataMapperBuilder();
        $builder->addLocator($serviceLocator);
        $builder->build();

        $obj = new ObjectWithExternalHook();
        $obj->getName();

        $this->assertTrue($hookService->called);
        $this->assertEquals(['name' => 'Test'], $hookService->receivedData);
    }

    public function testMultipleHooks(): void
    {
        $dataSource = new class {
            public function getData(): array
            {
                return ['data' => 'test'];
            }
        };

        $serviceLocator = new ArrayServiceLocator([
            'multiHookSource' => $dataSource,
        ]);

        $builder = new DataMapperBuilder();
        $builder->addLocator($serviceLocator);
        $builder->build();

        $obj = new ObjectWithMultipleHooks();
        $obj->getData();

        $this->assertEquals(['hook1', 'hook2', 'hook3'], $obj->executionOrder);
    }

    public function testHookWithArgs(): void
    {
        $dataSource = new class {
            public function getData(): array
            {
                return ['name' => 'Test'];
            }
        };

        $serviceLocator = new ArrayServiceLocator([
            'argsSource' => $dataSource,
        ]);

        $builder = new DataMapperBuilder();
        $builder->addLocator($serviceLocator);
        $builder->build();

        ContextRegistry::set('hookContext', 'context_value');

        $obj = new ObjectWithHookArgs();
        $obj->getName();

        $this->assertTrue($obj->hookCalled);
        $this->assertEquals('context_value', $obj->contextValue);
    }

    public function testHookOnNestedObject(): void
    {
        $dataSource = new class {
            public function getData(): array
            {
                return [
                    'title' => 'Parent',
                    'child' => ['name' => 'ChildName'],
                ];
            }
        };

        $serviceLocator = new ArrayServiceLocator([
            'nestedSource' => $dataSource,
        ]);

        $builder = new DataMapperBuilder();
        $builder->addLocator($serviceLocator);
        $builder->build();

        $parent = new ParentWithNestedHook();
        $parent->getTitle();
        $child = $parent->getChild();

        $this->assertNotNull($child);
        $this->assertTrue($child->hookCalled);
    }
}

// Test fixtures

#[DataSourcesStore([
    new MultiPropDataSource(id: 'personSource', class: 'personSource', method: 'getData'),
])]
#[PropertyHydratingHook(before_hydrate_object: 'onBeforeHydrate')]
class PersonWithBeforeHook
{
    use LoadableTrait;

    public bool $beforeHookCalled = false;
    public ?array $rawDataReceived = null;

    #[DataSourceRef(id: 'personSource')]
    private ?string $firstName = null;

    #[DataSourceRef(id: 'personSource')]
    private ?string $lastName = null;

    public function onBeforeHydrate(array $rawData): void
    {
        $this->beforeHookCalled = true;
        $this->rawDataReceived = $rawData;
    }

    public function getFirstName(): ?string
    {
        $this->loadProperty('firstName');
        return $this->firstName;
    }
}

#[DataSourcesStore([
    new MultiPropDataSource(id: 'personSource', class: 'personSource', method: 'getData'),
])]
#[PropertyHydratingHook(after_hydrate_object: 'onAfterHydrate')]
class PersonWithAfterHook
{
    use LoadableTrait;

    public bool $afterHookCalled = false;
    public ?string $fullName = null;

    #[DataSourceRef(id: 'personSource')]
    private ?string $firstName = null;

    #[DataSourceRef(id: 'personSource')]
    private ?string $lastName = null;

    public function onAfterHydrate(?object $object, array $rawData): void
    {
        $this->afterHookCalled = true;
        $this->fullName = trim(($this->firstName ?? '') . ' ' . ($this->lastName ?? ''));
    }

    public function getFirstName(): ?string
    {
        $this->loadProperty('firstName');
        return $this->firstName;
    }
}

#[DataSourcesStore([
    new MultiPropDataSource(id: 'orderSource', class: 'orderSource', method: 'getData'),
])]
#[PropertyHydratingHook(before_hydrate_object: 'onBefore', after_hydrate_object: 'onAfter')]
class ObjectWithBothHooks
{
    use LoadableTrait;

    public array $executionOrder = [];

    #[DataSourceRef(id: 'orderSource')]
    private ?string $value = null;

    public function onBefore(array $rawData): void
    {
        $this->executionOrder[] = 'before';
    }

    public function onAfter(?object $object, array $rawData): void
    {
        $this->executionOrder[] = 'after';
    }

    public function getValue(): ?string
    {
        $this->loadProperty('value');
        return $this->value;
    }
}

#[DataSourcesStore([
    new MultiPropDataSource(id: 'dataSource', class: 'dataSource', method: 'getData'),
])]
#[PropertyHydratingHook(before_hydrate_object: 'onBeforeHydrate', class: 'externalHookService')]
class ObjectWithExternalHook
{
    use LoadableTrait;

    #[DataSourceRef(id: 'dataSource')]
    private ?string $name = null;

    public function getName(): ?string
    {
        $this->loadProperty('name');
        return $this->name;
    }
}

#[DataSourcesStore([
    new MultiPropDataSource(id: 'multiHookSource', class: 'multiHookSource', method: 'getData'),
])]
#[PropertyHydratingHook(before_hydrate_object: 'hook1')]
#[PropertyHydratingHook(before_hydrate_object: 'hook2')]
#[PropertyHydratingHook(after_hydrate_object: 'hook3')]
class ObjectWithMultipleHooks
{
    use LoadableTrait;

    public array $executionOrder = [];

    #[DataSourceRef(id: 'multiHookSource')]
    private ?string $data = null;

    public function hook1(array $rawData): void
    {
        $this->executionOrder[] = 'hook1';
    }

    public function hook2(array $rawData): void
    {
        $this->executionOrder[] = 'hook2';
    }

    public function hook3(?object $object, array $rawData): void
    {
        $this->executionOrder[] = 'hook3';
    }

    public function getData(): ?string
    {
        $this->loadProperty('data');
        return $this->data;
    }
}

#[DataSourcesStore([
    new MultiPropDataSource(id: 'argsSource', class: 'argsSource', method: 'getData'),
])]
#[PropertyHydratingHook(before_hydrate_object: 'hookWithContext', args: ["expr(context('hookContext'))"])]
class ObjectWithHookArgs
{
    use LoadableTrait;

    public bool $hookCalled = false;
    public mixed $contextValue = null;

    #[DataSourceRef(id: 'argsSource')]
    private ?string $name = null;

    public function hookWithContext(array $rawData, mixed $contextArg = null): void
    {
        $this->hookCalled = true;
        $this->contextValue = $contextArg;
    }

    public function getName(): ?string
    {
        $this->loadProperty('name');
        return $this->name;
    }
}

#[PropertyHydratingHook(before_hydrate_object: 'onBeforeHydrate')]
class NestedChildWithHook
{
    public bool $hookCalled = false;
    public ?string $name = null;

    public function onBeforeHydrate(array $rawData): void
    {
        $this->hookCalled = true;
    }
}

#[DataSourcesStore([
    new MultiPropDataSource(id: 'nestedSource', class: 'nestedSource', method: 'getData'),
])]
class ParentWithNestedHook
{
    use LoadableTrait;

    #[DataSourceRef(id: 'nestedSource')]
    private ?string $title = null;

    #[\Kassko\DataMapper\Attribute\Property(class: NestedChildWithHook::class)]
    #[DataSourceRef(id: 'nestedSource')]
    private ?NestedChildWithHook $child = null;

    public function getTitle(): ?string
    {
        $this->loadProperty('title');
        return $this->title;
    }

    public function getChild(): ?NestedChildWithHook
    {
        $this->loadProperty('child');
        return $this->child;
    }
}
