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

namespace Kassko\DataMapper\Tests\Integration\Features\PropertyMappingRegistry;

use Kassko\DataMapper\Attribute\MultiPropDataSource;
use Kassko\DataMapper\Attribute\Property;
use Kassko\DataMapper\DataMapperBuilder;
use Kassko\DataMapper\Hydrator;
use Kassko\DataMapper\Registry\PropertyMappingRegistry;
use PHPUnit\Framework\TestCase;

class PropertyMappingRegistryTest extends TestCase
{
    private Hydrator $hydrator;

    protected function setUp(): void
    {
        $dataMapper = (new DataMapperBuilder())->build();
        $this->hydrator = $dataMapper->getHydrator();
    }

    protected function tearDown(): void
    {
        PropertyMappingRegistry::reset();
    }

    public function testBidirectionalMappingAfterHydration(): void
    {
        $rawData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email_address' => 'john.doe@example.com',
        ];

        $user = $this->hydrator->hydrate(UserWithMappedFields::class, $rawData);

        // Verify hydration worked
        $this->assertEquals('John', $user->firstName);
        $this->assertEquals('Doe', $user->lastName);
        $this->assertEquals('john.doe@example.com', $user->email);

        // Verify bidirectional mapping: property -> sourceField
        $this->assertEquals('first_name', PropertyMappingRegistry::getSourceField($user, 'firstName'));
        $this->assertEquals('last_name', PropertyMappingRegistry::getSourceField($user, 'lastName'));
        $this->assertEquals('email_address', PropertyMappingRegistry::getSourceField($user, 'email'));

        // Verify bidirectional mapping: sourceField -> property
        $this->assertEquals('firstName', PropertyMappingRegistry::getPropertyName($user, 'first_name'));
        $this->assertEquals('lastName', PropertyMappingRegistry::getPropertyName($user, 'last_name'));
        $this->assertEquals('email', PropertyMappingRegistry::getPropertyName($user, 'email_address'));
    }

    public function testMappingWithDefaultSourceField(): void
    {
        $rawData = [
            'name' => 'Test Product',
            'price' => 99.99,
        ];

        $product = $this->hydrator->hydrate(ProductWithDefaults::class, $rawData);

        // Verify hydration worked (same property name as source field)
        $this->assertEquals('Test Product', $product->name);
        $this->assertEquals(99.99, $product->price);

        // When no sourceField is specified, property name is used as source field
        $this->assertEquals('name', PropertyMappingRegistry::getSourceField($product, 'name'));
        $this->assertEquals('price', PropertyMappingRegistry::getSourceField($product, 'price'));

        // Reverse lookup also works
        $this->assertEquals('name', PropertyMappingRegistry::getPropertyName($product, 'name'));
        $this->assertEquals('price', PropertyMappingRegistry::getPropertyName($product, 'price'));
    }

    public function testGetAllMappingsAfterHydration(): void
    {
        $rawData = [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email_address' => 'jane@example.com',
        ];

        $user = $this->hydrator->hydrate(UserWithMappedFields::class, $rawData);

        $allMappings = PropertyMappingRegistry::getAllMappings($user);

        $this->assertArrayHasKey('firstName', $allMappings);
        $this->assertArrayHasKey('lastName', $allMappings);
        $this->assertArrayHasKey('email', $allMappings);
        $this->assertEquals('first_name', $allMappings['firstName']);
        $this->assertEquals('last_name', $allMappings['lastName']);
        $this->assertEquals('email_address', $allMappings['email']);
    }

    public function testGetAllReverseMappingsAfterHydration(): void
    {
        $rawData = [
            'first_name' => 'Bob',
            'last_name' => 'Johnson',
            'email_address' => 'bob@example.com',
        ];

        $user = $this->hydrator->hydrate(UserWithMappedFields::class, $rawData);

        $reverseMappings = PropertyMappingRegistry::getAllReverseMappings($user);

        $this->assertArrayHasKey('first_name', $reverseMappings);
        $this->assertArrayHasKey('last_name', $reverseMappings);
        $this->assertArrayHasKey('email_address', $reverseMappings);
        $this->assertEquals('firstName', $reverseMappings['first_name']);
        $this->assertEquals('lastName', $reverseMappings['last_name']);
        $this->assertEquals('email', $reverseMappings['email_address']);
    }

    public function testMixedMappingWithSomeDefaults(): void
    {
        $rawData = [
            'full_name' => 'Alice Wonder',
            'age' => 30, // Same as property name
        ];

        $person = $this->hydrator->hydrate(PersonMixedMapping::class, $rawData);

        $this->assertEquals('Alice Wonder', $person->name);
        $this->assertEquals(30, $person->age);

        // Mapped field
        $this->assertEquals('full_name', PropertyMappingRegistry::getSourceField($person, 'name'));
        $this->assertEquals('name', PropertyMappingRegistry::getPropertyName($person, 'full_name'));

        // Default (same name)
        $this->assertEquals('age', PropertyMappingRegistry::getSourceField($person, 'age'));
        $this->assertEquals('age', PropertyMappingRegistry::getPropertyName($person, 'age'));
    }

    public function testDifferentObjectsHaveIndependentMappings(): void
    {
        $user1 = $this->hydrator->hydrate(UserWithMappedFields::class, [
            'first_name' => 'User1',
            'last_name' => 'One',
            'email_address' => 'user1@example.com',
        ]);

        $user2 = $this->hydrator->hydrate(UserWithMappedFields::class, [
            'first_name' => 'User2',
            'last_name' => 'Two',
            'email_address' => 'user2@example.com',
        ]);

        // Both objects have independent mappings
        $this->assertEquals('first_name', PropertyMappingRegistry::getSourceField($user1, 'firstName'));
        $this->assertEquals('first_name', PropertyMappingRegistry::getSourceField($user2, 'firstName'));

        // Verify values are different
        $this->assertEquals('User1', $user1->firstName);
        $this->assertEquals('User2', $user2->firstName);
    }
}

#[MultiPropDataSource(
    class: 'this',
    method: 'provideData',
)]
class UserWithMappedFields
{
    #[Property(sourceField: 'first_name')]
    public string $firstName = '';

    #[Property(sourceField: 'last_name')]
    public string $lastName = '';

    #[Property(sourceField: 'email_address')]
    public string $email = '';
}

class ProductWithDefaults
{
    public string $name = '';
    public float $price = 0.0;
}

class PersonMixedMapping
{
    #[Property(sourceField: 'full_name')]
    public string $name = '';

    // No sourceField - uses property name as default
    public int $age = 0;
}
