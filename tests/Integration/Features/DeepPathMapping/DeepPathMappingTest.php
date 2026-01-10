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

namespace Kassko\DataMapper\Tests\Integration\Features\DeepPathMapping;

use Kassko\DataMapper\DataMapper;
use Kassko\DataMapper\DataMapperBuilder;
use Kassko\DataMapper\Registry\LoaderRegistry;
use Kassko\DataMapper\Tests\TestHelpers\LocalFixtureAutoloadTrait;
use Kassko\DataMapper\Tests\Integration\Features\DeepPathMapping\Fixtures\AddressDto;
use Kassko\DataMapper\Tests\Integration\Features\DeepPathMapping\Fixtures\AddressWithStreetDto;
use Kassko\DataMapper\Tests\Integration\Features\DeepPathMapping\Fixtures\PersonDto;
use Kassko\DataMapper\Tests\Integration\Features\DeepPathMapping\Fixtures\PersonWithDeepAddressDto;
use Kassko\DataMapper\Tests\Integration\Features\DeepPathMapping\Fixtures\PersonWithFlattenedAddress;
use Kassko\DataMapper\Tests\Integration\Features\DeepPathMapping\Fixtures\PersonWithDeeplyFlattenedAddress;
use Kassko\DataMapper\Tests\Integration\Features\DeepPathMapping\Fixtures\StreetDto;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for deep path mapping feature.
 * 
 * Deep path mapping allows properties to be mapped from nested structures
 * using dot notation (e.g., "address.street.number").
 * 
 * This feature should work for:
 * - DTO sources (via ObjectMapper)
 * - Raw data arrays (via Hydrator)
 */
class DeepPathMappingTest extends TestCase
{
    use LocalFixtureAutoloadTrait;

    private DataMapper $dataMapper;

    protected function setUp(): void
    {
        $builder = new DataMapperBuilder();
        $this->dataMapper = $builder->build();
    }

    protected function tearDown(): void
    {
        LoaderRegistry::clear();
    }

    // =========================================================================
    // DTO Source Tests (ObjectMapper)
    // =========================================================================

    public function testDeepPathMappingFromDto_TwoLevelNesting(): void
    {
        $addressDto = new AddressDto(
            street: '123 Main Street',
            city: 'New York',
            postalCode: '10001',
            country: 'USA'
        );
        
        $personDto = new PersonDto(
            firstName: 'John',
            lastName: 'Doe',
            address: $addressDto,
            email: 'john@example.com'
        );

        $objectMapper = $this->dataMapper->getObjectMapper();
        $person = $objectMapper->map(PersonWithFlattenedAddress::class, $personDto);

        $this->assertInstanceOf(PersonWithFlattenedAddress::class, $person);
        $this->assertEquals('John', $person->getFirstName());
        $this->assertEquals('Doe', $person->getLastName());
        $this->assertEquals('123 Main Street', $person->getStreet());
        $this->assertEquals('New York', $person->getCity());
        $this->assertEquals('10001', $person->getPostalCode());
    }

    public function testDeepPathMappingFromDto_ThreeLevelNesting(): void
    {
        $streetDto = new StreetDto(
            name: 'Main Street',
            number: 123,
            type: 'Avenue'
        );
        
        $addressDto = new AddressWithStreetDto(
            street: $streetDto,
            city: 'Los Angeles',
            postalCode: '90001',
            country: 'USA'
        );
        
        $personDto = new PersonWithDeepAddressDto(
            firstName: 'Jane',
            lastName: 'Smith',
            address: $addressDto
        );

        $objectMapper = $this->dataMapper->getObjectMapper();
        $person = $objectMapper->map(PersonWithDeeplyFlattenedAddress::class, $personDto);

        $this->assertInstanceOf(PersonWithDeeplyFlattenedAddress::class, $person);
        $this->assertEquals('Jane', $person->getFirstName());
        $this->assertEquals('Smith', $person->getLastName());
        $this->assertEquals('Main Street', $person->getStreetName());
        $this->assertEquals(123, $person->getStreetNumber());
        $this->assertEquals('Los Angeles', $person->getCity());
    }

    public function testDeepPathMappingFromDto_NullNestedObject(): void
    {
        $personDto = new PersonDto(
            firstName: 'John',
            lastName: 'Doe',
            address: null,
            email: 'john@example.com'
        );

        $objectMapper = $this->dataMapper->getObjectMapper();
        $person = $objectMapper->map(PersonWithFlattenedAddress::class, $personDto);

        $this->assertEquals('John', $person->getFirstName());
        $this->assertEquals('Doe', $person->getLastName());
        // Deep path properties should be null when parent is null
        $this->assertNull($person->getStreet());
        $this->assertNull($person->getCity());
        $this->assertNull($person->getPostalCode());
    }

    public function testDeepPathMappingFromDto_PartiallyNullNestedPath(): void
    {
        $addressDto = new AddressWithStreetDto(
            street: null, // Street is null
            city: 'Chicago',
            postalCode: '60601',
            country: 'USA'
        );
        
        $personDto = new PersonWithDeepAddressDto(
            firstName: 'Bob',
            lastName: 'Johnson',
            address: $addressDto
        );

        $objectMapper = $this->dataMapper->getObjectMapper();
        $person = $objectMapper->map(PersonWithDeeplyFlattenedAddress::class, $personDto);

        $this->assertEquals('Bob', $person->getFirstName());
        $this->assertEquals('Johnson', $person->getLastName());
        // 3-level paths should be null when intermediate object is null
        $this->assertNull($person->getStreetName());
        $this->assertNull($person->getStreetNumber());
        // 2-level path should still work
        $this->assertEquals('Chicago', $person->getCity());
    }

    public function testDeepPathMappingFromDto_MapToExisting(): void
    {
        $addressDto = new AddressDto(
            street: 'Updated Street',
            city: 'Updated City',
            postalCode: '99999'
        );
        
        $personDto = new PersonDto(
            firstName: 'Updated',
            lastName: 'Person',
            address: $addressDto
        );

        // Create existing object with original values
        $existingPerson = new PersonWithFlattenedAddress();
        $existingPerson->setFirstName('Original');
        $existingPerson->setLastName('Name');
        $existingPerson->setStreet('Original Street');

        $objectMapper = $this->dataMapper->getObjectMapper();
        $result = $objectMapper->mapToExisting($existingPerson, $personDto);

        $this->assertSame($existingPerson, $result);
        $this->assertEquals('Updated', $existingPerson->getFirstName());
        $this->assertEquals('Person', $existingPerson->getLastName());
        $this->assertEquals('Updated Street', $existingPerson->getStreet());
        $this->assertEquals('Updated City', $existingPerson->getCity());
    }

    // =========================================================================
    // Raw Data Array Tests (Hydrator)
    // =========================================================================

    public function testDeepPathMappingFromRawData_TwoLevelNesting(): void
    {
        $rawData = [
            'firstName' => 'Alice',
            'lastName' => 'Wonder',
            'address' => [
                'street' => '456 Oak Avenue',
                'city' => 'Boston',
                'postalCode' => '02101',
            ],
        ];

        $hydrator = $this->dataMapper->getHydrator();
        $person = $hydrator->hydrate(PersonWithFlattenedAddress::class, $rawData);

        $this->assertInstanceOf(PersonWithFlattenedAddress::class, $person);
        $this->assertEquals('Alice', $person->getFirstName());
        $this->assertEquals('Wonder', $person->getLastName());
        $this->assertEquals('456 Oak Avenue', $person->getStreet());
        $this->assertEquals('Boston', $person->getCity());
        $this->assertEquals('02101', $person->getPostalCode());
    }

    public function testDeepPathMappingFromRawData_ThreeLevelNesting(): void
    {
        $rawData = [
            'firstName' => 'Charlie',
            'lastName' => 'Brown',
            'address' => [
                'street' => [
                    'name' => 'Peanut Lane',
                    'number' => 42,
                    'type' => 'Lane',
                ],
                'city' => 'Springfield',
                'postalCode' => '12345',
            ],
        ];

        $hydrator = $this->dataMapper->getHydrator();
        $person = $hydrator->hydrate(PersonWithDeeplyFlattenedAddress::class, $rawData);

        $this->assertInstanceOf(PersonWithDeeplyFlattenedAddress::class, $person);
        $this->assertEquals('Charlie', $person->getFirstName());
        $this->assertEquals('Brown', $person->getLastName());
        $this->assertEquals('Peanut Lane', $person->getStreetName());
        $this->assertEquals(42, $person->getStreetNumber());
        $this->assertEquals('Springfield', $person->getCity());
    }

    public function testDeepPathMappingFromRawData_NullNestedArray(): void
    {
        $rawData = [
            'firstName' => 'David',
            'lastName' => 'Smith',
            'address' => null,
        ];

        $hydrator = $this->dataMapper->getHydrator();
        $person = $hydrator->hydrate(PersonWithFlattenedAddress::class, $rawData);

        $this->assertEquals('David', $person->getFirstName());
        $this->assertEquals('Smith', $person->getLastName());
        // Deep path properties should be null when parent array is null
        $this->assertNull($person->getStreet());
        $this->assertNull($person->getCity());
        $this->assertNull($person->getPostalCode());
    }

    public function testDeepPathMappingFromRawData_MissingNestedKey(): void
    {
        $rawData = [
            'firstName' => 'Eve',
            'lastName' => 'Adams',
            // 'address' key is missing entirely
        ];

        $hydrator = $this->dataMapper->getHydrator();
        $person = $hydrator->hydrate(PersonWithFlattenedAddress::class, $rawData);

        $this->assertEquals('Eve', $person->getFirstName());
        $this->assertEquals('Adams', $person->getLastName());
        // Deep path properties should be null when parent key is missing
        $this->assertNull($person->getStreet());
        $this->assertNull($person->getCity());
        $this->assertNull($person->getPostalCode());
    }

    public function testDeepPathMappingFromRawData_PartiallyMissingPath(): void
    {
        $rawData = [
            'firstName' => 'Frank',
            'lastName' => 'Miller',
            'address' => [
                // 'street' key is missing - should result in null for street.name and street.number
                'city' => 'Seattle',
                'postalCode' => '98101',
            ],
        ];

        $hydrator = $this->dataMapper->getHydrator();
        $person = $hydrator->hydrate(PersonWithDeeplyFlattenedAddress::class, $rawData);

        $this->assertEquals('Frank', $person->getFirstName());
        $this->assertEquals('Miller', $person->getLastName());
        // 3-level paths should be null when intermediate key is missing
        $this->assertNull($person->getStreetName());
        $this->assertNull($person->getStreetNumber());
        // 2-level path should still work
        $this->assertEquals('Seattle', $person->getCity());
    }

    public function testDeepPathMappingFromRawData_HydrateExisting(): void
    {
        $rawData = [
            'firstName' => 'Updated',
            'lastName' => 'Data',
            'address' => [
                'street' => 'New Street',
                'city' => 'New City',
                'postalCode' => '11111',
            ],
        ];

        // Create existing object
        $existingPerson = new PersonWithFlattenedAddress();
        $existingPerson->setFirstName('Original');
        $existingPerson->setStreet('Original Street');

        $hydrator = $this->dataMapper->getHydrator();
        $result = $hydrator->hydrateExisting($existingPerson, $rawData);

        $this->assertSame($existingPerson, $result);
        $this->assertEquals('Updated', $existingPerson->getFirstName());
        $this->assertEquals('Data', $existingPerson->getLastName());
        $this->assertEquals('New Street', $existingPerson->getStreet());
        $this->assertEquals('New City', $existingPerson->getCity());
    }

    // =========================================================================
    // Edge Cases
    // =========================================================================

    public function testDeepPathMappingWithEmptyPath(): void
    {
        // Test that regular (non-deep) paths still work
        $personDto = new PersonDto(
            firstName: 'Simple',
            lastName: 'Case',
            address: null
        );

        $objectMapper = $this->dataMapper->getObjectMapper();
        $person = $objectMapper->map(PersonWithFlattenedAddress::class, $personDto);

        $this->assertEquals('Simple', $person->getFirstName());
        $this->assertEquals('Case', $person->getLastName());
    }

    public function testDeepPathMappingWithEmptyStringValues(): void
    {
        $addressDto = new AddressDto(
            street: '',
            city: '',
            postalCode: ''
        );
        
        $personDto = new PersonDto(
            firstName: 'Empty',
            lastName: 'Values',
            address: $addressDto
        );

        $objectMapper = $this->dataMapper->getObjectMapper();
        $person = $objectMapper->map(PersonWithFlattenedAddress::class, $personDto);

        $this->assertEquals('Empty', $person->getFirstName());
        $this->assertEquals('', $person->getStreet());
        $this->assertEquals('', $person->getCity());
    }

    public function testDeepPathMappingFromRawDataWithEmptyArrays(): void
    {
        $rawData = [
            'firstName' => 'Test',
            'lastName' => 'User',
            'address' => [
                'street' => [],
                'city' => 'City',
                'postalCode' => '00000',
            ],
        ];

        $hydrator = $this->dataMapper->getHydrator();
        $person = $hydrator->hydrate(PersonWithDeeplyFlattenedAddress::class, $rawData);

        $this->assertEquals('Test', $person->getFirstName());
        // When street is an empty array, name and number should be null
        $this->assertNull($person->getStreetName());
        $this->assertNull($person->getStreetNumber());
        $this->assertEquals('City', $person->getCity());
    }
}
