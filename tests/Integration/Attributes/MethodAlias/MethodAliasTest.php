<?php

declare(strict_types=1);

/*
 * This file is part of DataMapper.
 *
 * Copyright 2025 kassko
 *
 * For the full copyright and license information,
 * please view the LICENSE and NOTICE files that were distributed with this source code.
 */

namespace Kassko\DataMapper\Tests\Integration\Attributes;

use Kassko\DataMapper\ArrayServiceLocator;
use Kassko\DataMapper\Attribute\DataSource;
use Kassko\DataMapper\Attribute\DataSourceRef;
use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\Attribute\MethodAlias;
use Kassko\DataMapper\Attribute\MultiPropDataSource;
use Kassko\DataMapper\Attribute\SinglePropDataSource;
use Kassko\DataMapper\DataMapperBuilder;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;
use Kassko\DataMapper\Registry\ContextRegistry;
use Kassko\DataMapper\Registry\LoaderRegistry;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for the MethodAlias attribute.
 *
 * Tests that MethodAlias works correctly with:
 * - SinglePropDataSource via methodAlias parameter
 * - MultiPropDataSource via methodAlias parameter  
 * - DataSource via methodAlias parameter
 * - ##object reference for current object methods
 * - Cascading to child classes
 */
class MethodAliasTest extends TestCase
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

    /**
     * Test MethodAlias with SinglePropDataSource using methodAlias parameter.
     */
    public function testMethodAliasWithSinglePropDataSource(): void
    {
        $userService = new class {
            public function fetchUserEmail(int $id): string
            {
                return "user{$id}@example.com";
            }
        };

        $serviceLocator = new ArrayServiceLocator([
            'user.service' => $userService,
        ]);

        $builder = new DataMapperBuilder();
        $builder->addServiceLocator($serviceLocator);
        $builder->build();

        $user = new UserWithMethodAliasSingleProp();
        $user->id = 42;

        $email = $user->getEmail();

        $this->assertEquals('user42@example.com', $email);
    }

    /**
     * Test MethodAlias with MultiPropDataSource using methodAlias parameter.
     */
    public function testMethodAliasWithMultiPropDataSource(): void
    {
        $personService = new class {
            public function fetchPersonData(int $id): array
            {
                return [
                    'name' => "Person {$id}",
                    'email' => "person{$id}@example.com",
                    'role' => 'user',
                ];
            }
        };

        $serviceLocator = new ArrayServiceLocator([
            'person.service' => $personService,
        ]);

        $builder = new DataMapperBuilder();
        $builder->addServiceLocator($serviceLocator);
        $builder->build();

        $person = new PersonWithMethodAliasMultiProp();
        $person->id = 123;

        $name = $person->getName();
        $email = $person->getEmail();

        $this->assertEquals('Person 123', $name);
        $this->assertEquals('person123@example.com', $email);
    }

    /**
     * Test MethodAlias with ##object for current object method.
     */
    public function testMethodAliasWithObjectReference(): void
    {
        $builder = new DataMapperBuilder();
        $builder->build();

        $entity = new EntityWithObjectMethodAlias();
        $entity->rawData = ['firstName' => 'John', 'lastName' => 'Doe'];

        $fullName = $entity->getFullName();

        $this->assertEquals('John Doe', $fullName);
    }

    /**
     * Test MethodAlias cascading to child classes.
     */
    public function testMethodAliasCascading(): void
    {
        $baseService = new class {
            public function fetchBaseData(): array
            {
                return ['base' => 'value'];
            }
        };

        $serviceLocator = new ArrayServiceLocator([
            'base.service' => $baseService,
        ]);

        $builder = new DataMapperBuilder();
        $builder->addServiceLocator($serviceLocator);
        $builder->build();

        $child = new ChildEntityWithInheritedMethodAlias();

        $data = $child->getBaseData();

        $this->assertEquals(['base' => 'value'], $data);
    }

    /**
     * Test MethodAlias with cascade=false does not cascade.
     */
    public function testMethodAliasNoCascade(): void
    {
        $this->expectException(\Kassko\DataMapper\Exception\MethodAliasNotFoundException::class);

        $service = new class {
            public function getData(): array
            {
                return ['data' => 'value'];
            }
        };

        $serviceLocator = new ArrayServiceLocator([
            'non.cascade.service' => $service,
        ]);

        $builder = new DataMapperBuilder();
        $builder->addServiceLocator($serviceLocator);
        $builder->build();

        $child = new ChildEntityWithNonCascadingMethodAlias();
        $child->getData(); // Should throw MethodAliasNotFoundException
    }

    /**
     * Test multiple MethodAlias attributes on same class.
     */
    public function testMultipleMethodAliases(): void
    {
        $service1 = new class {
            public function fetchName(int $id): string
            {
                return "Name {$id}";
            }
        };

        $service2 = new class {
            public function fetchAge(int $id): int
            {
                return $id * 2;
            }
        };

        $serviceLocator = new ArrayServiceLocator([
            'name.service' => $service1,
            'age.service' => $service2,
        ]);

        $builder = new DataMapperBuilder();
        $builder->addServiceLocator($serviceLocator);
        $builder->build();

        $entity = new EntityWithMultipleMethodAliases();
        $entity->id = 25;

        $name = $entity->getName();
        $age = $entity->getAge();

        $this->assertEquals('Name 25', $name);
        $this->assertEquals(50, $age);
    }
}

// ============ Test Fixtures ============

/**
 * User entity using MethodAlias with SinglePropDataSource.
 */
#[MethodAlias(name: 'fetchUserEmail', class: 'user.service', method: 'fetchUserEmail')]
class UserWithMethodAliasSingleProp
{
    use LoadableTrait;

    public int $id;

    #[SinglePropDataSource(methodAlias: 'fetchUserEmail', args: ['#id'])]
    private ?string $email = null;

    public function getEmail(): ?string
    {
        $this->loadProperty('email');
        return $this->email;
    }
}

/**
 * Person entity using MethodAlias with MultiPropDataSource.
 */
#[MethodAlias(name: 'fetchPersonData', class: 'person.service', method: 'fetchPersonData')]
#[DataSourcesStore([
    new MultiPropDataSource(id: 'personData', methodAlias: 'fetchPersonData', args: ['#id']),
])]
class PersonWithMethodAliasMultiProp
{
    use LoadableTrait;

    public int $id;

    #[DataSourceRef(id: 'personData')]
    private ?string $name = null;

    #[DataSourceRef(id: 'personData')]
    private ?string $email = null;

    public function getName(): ?string
    {
        $this->loadProperty('name');
        return $this->name;
    }

    public function getEmail(): ?string
    {
        $this->loadProperty('email');
        return $this->email;
    }
}

/**
 * Entity using MethodAlias with ##object for current object method.
 */
#[MethodAlias(name: 'buildFullName', class: '##object', method: 'computeFullName')]
class EntityWithObjectMethodAlias
{
    use LoadableTrait;

    public array $rawData = [];

    #[SinglePropDataSource(methodAlias: 'buildFullName')]
    private ?string $fullName = null;

    public function getFullName(): ?string
    {
        $this->loadProperty('fullName');
        return $this->fullName;
    }

    /**
     * Method on current object referenced by MethodAlias.
     */
    public function computeFullName(): string
    {
        return ($this->rawData['firstName'] ?? '') . ' ' . ($this->rawData['lastName'] ?? '');
    }
}

/**
 * Base entity with cascading MethodAlias.
 */
#[MethodAlias(name: 'fetchBaseData', class: 'base.service', method: 'fetchBaseData', cascade: true)]
abstract class BaseEntityWithCascadingMethodAlias
{
    use LoadableTrait;
}

/**
 * Child entity that inherits MethodAlias from parent.
 */
class ChildEntityWithInheritedMethodAlias extends BaseEntityWithCascadingMethodAlias
{
    #[SinglePropDataSource(methodAlias: 'fetchBaseData')]
    private ?array $baseData = null;

    public function getBaseData(): ?array
    {
        $this->loadProperty('baseData');
        return $this->baseData;
    }
}

/**
 * Base entity with non-cascading MethodAlias.
 */
#[MethodAlias(name: 'nonCascadeData', class: 'non.cascade.service', method: 'getData', cascade: false)]
abstract class BaseEntityWithNonCascadingMethodAlias
{
    use LoadableTrait;
}

/**
 * Child entity that should NOT inherit non-cascading MethodAlias.
 */
class ChildEntityWithNonCascadingMethodAlias extends BaseEntityWithNonCascadingMethodAlias
{
    #[SinglePropDataSource(methodAlias: 'nonCascadeData')]
    private ?array $data = null;

    public function getData(): ?array
    {
        $this->loadProperty('data');
        return $this->data;
    }
}

/**
 * Entity with multiple MethodAlias attributes.
 */
#[MethodAlias(name: 'fetchName', class: 'name.service', method: 'fetchName')]
#[MethodAlias(name: 'fetchAge', class: 'age.service', method: 'fetchAge')]
class EntityWithMultipleMethodAliases
{
    use LoadableTrait;

    public int $id;

    #[SinglePropDataSource(methodAlias: 'fetchName', args: ['#id'])]
    private ?string $name = null;

    #[SinglePropDataSource(methodAlias: 'fetchAge', args: ['#id'])]
    private ?int $age = null;

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
}
