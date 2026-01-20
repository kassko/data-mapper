<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Tests\Integration\Attributes\PropertyCheckItemClass;

use Kassko\DataMapper\Attribute\Property;
use Kassko\DataMapper\DataMapper;
use Kassko\DataMapper\DataMapperBuilder;
use Kassko\DataMapper\Tests\Integration\Attributes\PropertyCheckItemClass\Fixtures\Address;
use Kassko\DataMapper\Tests\Integration\Attributes\PropertyCheckItemClass\Fixtures\PersonWithItemClass;
use Kassko\DataMapper\Tests\Integration\Attributes\PropertyCheckItemClass\Fixtures\PersonWithClassForSingleObject;
use Kassko\DataMapper\Tests\Integration\Attributes\PropertyCheckItemClass\Fixtures\PersonWithTypehintFallback;
use Kassko\DataMapper\Tests\Integration\Attributes\PropertyCheckItemClass\Fixtures\PersonWithScalarItemClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Property::itemClass and Property::class behavior.
 */
class ItemClassTest extends TestCase
{
    private DataMapper $dataMapper;

    protected function setUp(): void
    {
        $this->dataMapper = (new DataMapperBuilder())->build();
    }

    /**
     * Test that itemClass is used to hydrate collection items
     */
    public function testItemClassHydratesCollectionItems(): void
    {
        $rawData = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'addresses' => [
                [
                    'street' => '123 Main St',
                    'city' => 'New York',
                    'postalCode' => '10001',
                ],
                [
                    'street' => '456 Oak Ave',
                    'city' => 'Los Angeles',
                    'postalCode' => '90001',
                ],
            ],
        ];

        $person = $this->dataMapper->getHydrator()->hydrate(PersonWithItemClass::class, $rawData);

        $this->assertEquals('John', $person->getFirstName());
        $this->assertEquals('Doe', $person->getLastName());
        
        $addresses = $person->getAddresses();
        $this->assertCount(2, $addresses);
        
        $this->assertInstanceOf(Address::class, $addresses[0]);
        $this->assertEquals('123 Main St', $addresses[0]->getStreet());
        $this->assertEquals('New York', $addresses[0]->getCity());
        $this->assertEquals('10001', $addresses[0]->getPostalCode());
        
        $this->assertInstanceOf(Address::class, $addresses[1]);
        $this->assertEquals('456 Oak Ave', $addresses[1]->getStreet());
        $this->assertEquals('Los Angeles', $addresses[1]->getCity());
        $this->assertEquals('90001', $addresses[1]->getPostalCode());
    }

    /**
     * Test that class is used for single object hydration
     */
    public function testClassHydratesSingleObject(): void
    {
        $rawData = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'address' => [
                'street' => '123 Main St',
                'city' => 'New York',
                'postalCode' => '10001',
            ],
        ];

        $person = $this->dataMapper->getHydrator()->hydrate(PersonWithClassForSingleObject::class, $rawData);

        $this->assertEquals('John', $person->getFirstName());
        $this->assertEquals('Doe', $person->getLastName());
        
        $address = $person->getAddress();
        $this->assertInstanceOf(Address::class, $address);
        $this->assertEquals('123 Main St', $address->getStreet());
        $this->assertEquals('New York', $address->getCity());
        $this->assertEquals('10001', $address->getPostalCode());
    }

    /**
     * Test that PHP typehint is used when Property::class is not set
     */
    public function testTypehintFallbackForSingleObject(): void
    {
        $rawData = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'main_address' => [
                'street' => '789 Pine Rd',
                'city' => 'Chicago',
                'postalCode' => '60601',
            ],
        ];

        $person = $this->dataMapper->getHydrator()->hydrate(PersonWithTypehintFallback::class, $rawData);

        $this->assertEquals('John', $person->getFirstName());
        $this->assertEquals('Doe', $person->getLastName());
        
        $address = $person->getMainAddress();
        $this->assertInstanceOf(Address::class, $address);
        $this->assertEquals('789 Pine Rd', $address->getStreet());
        $this->assertEquals('Chicago', $address->getCity());
        $this->assertEquals('60601', $address->getPostalCode());
    }

    /**
     * Test that itemClass with scalar type throws an exception
     */
    public function testItemClassWithScalarTypeThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('has itemClass defined but has scalar type');
        
        $rawData = [
            'firstName' => 'John',
            'invalidProperty' => [
                ['street' => 'test'],
            ],
        ];

        $this->dataMapper->getHydrator()->hydrate(PersonWithScalarItemClass::class, $rawData);
    }

    /**
     * Test that Property attribute validation works for itemClass
     */
    public function testPropertyItemClassCanBeUsedWithMappingValidation(): void
    {
        // Should not throw exception
        $property = new Property(
            itemClass: Address::class,
            mapping: ['source' => 'target']
        );
        
        $this->assertEquals(Address::class, $property->itemClass);
    }

    /**
     * Test that Property attribute allows class and itemClass together
     */
    public function testPropertyAllowsClassAndItemClassTogether(): void
    {
        // Should not throw exception - class for container, itemClass for items
        $property = new Property(
            class: 'ArrayCollection',
            itemClass: Address::class
        );
        
        $this->assertEquals('ArrayCollection', $property->class);
        $this->assertEquals(Address::class, $property->itemClass);
    }
}
