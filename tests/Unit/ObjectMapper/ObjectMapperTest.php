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

namespace Kassko\DataMapper\Tests\Unit;

use Kassko\DataMapper\DataMapper;
use Kassko\DataMapper\DataMapperBuilder;
use Kassko\DataMapper\ObjectMapper;
use Kassko\DataMapper\Tests\TestHelpers\LocalFixtureAutoloadTrait;
use Kassko\Sample\ObjectMapper\Person;
use Kassko\Sample\ObjectMapper\PersonDto;
use Kassko\Sample\ObjectMapper\PersonWithAddress;
use Kassko\Sample\ObjectMapper\PersonWithAddressDto;
use Kassko\Sample\ObjectMapper\AddressDto;
use PHPUnit\Framework\TestCase;

final class ObjectMapperTest extends TestCase
{
    use LocalFixtureAutoloadTrait;

    private DataMapper $dataMapper;
    private ObjectMapper $objectMapper;

    protected function setUp(): void
    {
        $builder = new DataMapperBuilder();
        $this->dataMapper = $builder->build();
        $this->objectMapper = $this->dataMapper->getObjectMapper();
    }

    public function testGetObjectMapperReturnsSameInstance(): void
    {
        $objectMapper1 = $this->dataMapper->getObjectMapper();
        $objectMapper2 = $this->dataMapper->getObjectMapper();

        $this->assertSame($objectMapper1, $objectMapper2);
    }

    public function testGetObjectMapperReturnsObjectMapperInstance(): void
    {
        $this->assertInstanceOf(ObjectMapper::class, $this->objectMapper);
    }

    public function testMapCreatesObjectFromDto(): void
    {
        $dto = new PersonDto(
            firstName: 'John',
            lastName: 'Doe',
            email: 'john@example.com',
            age: 30
        );

        $person = $this->objectMapper->map(Person::class, $dto);

        $this->assertInstanceOf(Person::class, $person);
        $this->assertEquals('John', $person->getFirstName());
        $this->assertEquals('Doe', $person->getLastName());
        $this->assertEquals('john@example.com', $person->getEmail());
        $this->assertEquals(30, $person->getAge());
    }

    public function testMapWithMissingFields(): void
    {
        $dto = new PersonDto(
            firstName: 'Alice'
        );

        $person = $this->objectMapper->map(Person::class, $dto);

        $this->assertEquals('Alice', $person->getFirstName());
        $this->assertEquals('', $person->getLastName());
        $this->assertNull($person->getEmail());
        $this->assertNull($person->getAge());
    }

    public function testMapToExistingObject(): void
    {
        $existingPerson = new Person();
        $existingPerson->setFirstName('Original');
        
        $dto = new PersonDto(
            firstName: 'Updated',
            lastName: 'Name',
            email: 'updated@example.com'
        );

        $result = $this->objectMapper->mapToExisting($existingPerson, $dto);

        $this->assertSame($existingPerson, $result);
        $this->assertEquals('Updated', $existingPerson->getFirstName());
        $this->assertEquals('Name', $existingPerson->getLastName());
        $this->assertEquals('updated@example.com', $existingPerson->getEmail());
    }

    public function testMapWithDeepPath(): void
    {
        $addressDto = new AddressDto(
            street: '123 Main St',
            city: 'New York',
            postalCode: '10001',
            country: 'USA'
        );
        
        $dto = new PersonWithAddressDto(
            firstName: 'John',
            lastName: 'Doe',
            address: $addressDto
        );

        $person = $this->objectMapper->map(PersonWithAddress::class, $dto);

        $this->assertInstanceOf(PersonWithAddress::class, $person);
        $this->assertEquals('John', $person->getFirstName());
        $this->assertEquals('Doe', $person->getLastName());
        $this->assertEquals('123 Main St', $person->getStreet());
        $this->assertEquals('New York', $person->getCity());
        $this->assertEquals('10001', $person->getPostalCode());
    }

    public function testMapWithNullNestedObject(): void
    {
        $dto = new PersonWithAddressDto(
            firstName: 'John',
            lastName: 'Doe',
            address: null
        );

        $person = $this->objectMapper->map(PersonWithAddress::class, $dto);

        $this->assertInstanceOf(PersonWithAddress::class, $person);
        $this->assertEquals('John', $person->getFirstName());
        $this->assertEquals('Doe', $person->getLastName());
        $this->assertNull($person->getStreet());
        $this->assertNull($person->getCity());
        $this->assertNull($person->getPostalCode());
    }
}
