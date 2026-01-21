<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Tests\Integration\Attributes\PropertyNativeTypehintReading;

use Kassko\DataMapper\DataMapper;
use Kassko\DataMapper\DataMapperBuilder;
use Kassko\DataMapper\Tests\Integration\Attributes\PropertyNativeTypehintReading\Fixtures\Address;
use Kassko\DataMapper\Tests\Integration\Attributes\PropertyNativeTypehintReading\Fixtures\AddressCollection;
use Kassko\DataMapper\Tests\Integration\Attributes\PropertyNativeTypehintReading\Fixtures\PersonWithNativeTypehint;
use Kassko\DataMapper\Tests\Integration\Attributes\PropertyNativeTypehintReading\Fixtures\PersonWithExplicitProperty;
use Kassko\DataMapper\Tests\Integration\Attributes\PropertyNativeTypehintReading\Fixtures\PersonWithSingleAddress;
use Kassko\DataMapper\Tests\Integration\Attributes\PropertyNativeTypehintReading\Fixtures\PersonWithInterfaceTypehint;
use Kassko\DataMapper\Tests\Integration\Attributes\PropertyNativeTypehintReading\Fixtures\PersonWithAbstractTypehint;
use Kassko\DataMapper\Tests\Integration\Attributes\PropertyNativeTypehintReading\Fixtures\PersonWithUnionType;
use PHPUnit\Framework\TestCase;

/**
 * Test native PHP typehint reading for automatic recursive hydration
 *
 * This test suite verifies that properties with class typehints but without explicit
 * #[Property] attributes are automatically hydrated using the typehint class.
 *
 * Bug scenario:
 * - A property like `private ?AddressCollection $addresses = null;` without #[Property]
 * - Before fix: Raw array data was passed to setter, causing type error
 * - After fix: Hydrator reads native typehint and creates an instance of the class
 */
class NativeTypehintReadingTest extends TestCase
{
    private DataMapper $dataMapper;

    protected function setUp(): void
    {
        $this->dataMapper = (new DataMapperBuilder())->build();
    }

    /**
     * Test the main bug scenario: native typehint for custom collection class without Property attribute
     *
     * This is the reproduction case for the bug:
     * - PersonWithNativeTypehint has `private ?AddressCollection $addresses = null;` without #[Property]
     * - The hydrator should use the native typehint to create an AddressCollection instance
     */
    public function testNativeTypehintForCustomCollectionClass(): void
    {
        $rawData = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'addresses' => [
                'street' => '123 Main St',
                'city' => 'New York',
                'postalCode' => '10001',
            ],
        ];

        // This should NOT throw a type error anymore
        $person = $this->dataMapper->getHydrator()->hydrate(PersonWithNativeTypehint::class, $rawData);

        $this->assertInstanceOf(PersonWithNativeTypehint::class, $person);
        $this->assertEquals('John', $person->getFirstName());
        $this->assertEquals('Doe', $person->getLastName());
        
        // The addresses property should be an AddressCollection instance
        $addresses = $person->getAddresses();
        $this->assertInstanceOf(AddressCollection::class, $addresses);
    }

    /**
     * Test that explicit Property attribute still works (comparison case)
     */
    public function testExplicitPropertyAttributeStillWorks(): void
    {
        $rawData = [
            'firstName' => 'Jane',
            'lastName' => 'Smith',
            'addresses' => [
                'street' => '456 Oak Ave',
                'city' => 'Los Angeles',
                'postalCode' => '90001',
            ],
        ];

        $person = $this->dataMapper->getHydrator()->hydrate(PersonWithExplicitProperty::class, $rawData);

        $this->assertInstanceOf(PersonWithExplicitProperty::class, $person);
        $this->assertEquals('Jane', $person->getFirstName());
        
        $addresses = $person->getAddresses();
        $this->assertInstanceOf(AddressCollection::class, $addresses);
    }

    /**
     * Test native typehint for single object (not collection)
     */
    public function testNativeTypehintForSingleObject(): void
    {
        $rawData = [
            'firstName' => 'Bob',
            'lastName' => 'Johnson',
            'mainAddress' => [
                'street' => '789 Pine Rd',
                'city' => 'Chicago',
                'postalCode' => '60601',
            ],
        ];

        $person = $this->dataMapper->getHydrator()->hydrate(PersonWithSingleAddress::class, $rawData);

        $this->assertInstanceOf(PersonWithSingleAddress::class, $person);
        $this->assertEquals('Bob', $person->getFirstName());
        
        $mainAddress = $person->getMainAddress();
        $this->assertInstanceOf(Address::class, $mainAddress);
        $this->assertEquals('789 Pine Rd', $mainAddress->getStreet());
        $this->assertEquals('Chicago', $mainAddress->getCity());
        $this->assertEquals('60601', $mainAddress->getPostalCode());
    }

    /**
     * Test that interface typehints do NOT trigger synthetic property creation
     * because interfaces are not instantiable.
     *
     * When the typehint is an interface, the hydrator cannot create a synthetic Property
     * because interfaces are not instantiable. The raw value will be passed to the setter,
     * which will cause a TypeError.
     */
    public function testInterfaceTypehintDoesNotTriggerSyntheticProperty(): void
    {
        $rawData = [
            'firstName' => 'Alice',
            'location' => [
                'street' => '321 Elm St',
                'city' => 'Boston',
            ],
        ];

        // Should throw TypeError because AddressableInterface is not instantiable
        // and the raw array value will be passed to the setter
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('must be of type');

        $this->dataMapper->getHydrator()->hydrate(PersonWithInterfaceTypehint::class, $rawData);
    }

    /**
     * Test that abstract class typehints do NOT trigger synthetic property creation
     * because abstract classes are not instantiable.
     *
     * When the typehint is an abstract class, the hydrator cannot create a synthetic Property
     * because abstract classes are not instantiable. The raw value will be passed to the setter,
     * which will cause a TypeError.
     */
    public function testAbstractTypehintDoesNotTriggerSyntheticProperty(): void
    {
        $rawData = [
            'firstName' => 'Charlie',
            'location' => [
                'name' => 'Home Base',
            ],
        ];

        // Should throw TypeError because AbstractLocation is not instantiable
        // and the raw array value will be passed to the setter
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('must be of type');

        $this->dataMapper->getHydrator()->hydrate(PersonWithAbstractTypehint::class, $rawData);
    }

    /**
     * Test union type uses first instantiable class
     */
    public function testUnionTypeUsesFirstInstantiableClass(): void
    {
        $rawData = [
            'firstName' => 'Diana',
            'location' => [
                'street' => '555 Cedar Ln',
                'city' => 'Seattle',
                'postalCode' => '98101',
            ],
        ];

        $person = $this->dataMapper->getHydrator()->hydrate(PersonWithUnionType::class, $rawData);

        $this->assertInstanceOf(PersonWithUnionType::class, $person);
        $this->assertEquals('Diana', $person->getFirstName());
        
        // Location should be hydrated as Address (first in union type)
        $location = $person->getLocation();
        $this->assertInstanceOf(Address::class, $location);
        $this->assertEquals('555 Cedar Ln', $location->getStreet());
    }

    /**
     * Test that null values are handled correctly
     */
    public function testNullValueIsNotHydrated(): void
    {
        $rawData = [
            'firstName' => 'Eve',
            'lastName' => 'Wilson',
            'addresses' => null,
        ];

        $person = $this->dataMapper->getHydrator()->hydrate(PersonWithNativeTypehint::class, $rawData);

        $this->assertInstanceOf(PersonWithNativeTypehint::class, $person);
        $this->assertEquals('Eve', $person->getFirstName());
        
        // addresses should be null
        $this->assertNull($person->getAddresses());
    }

    /**
     * Test that scalar values cause a TypeError because they cannot be hydrated
     * and the setter expects an object type.
     */
    public function testScalarValueCausesTypeError(): void
    {
        $rawData = [
            'firstName' => 'Frank',
            'lastName' => 'Brown',
            'addresses' => 'not an array',
        ];

        // Should throw TypeError because scalar value cannot be hydrated
        // and the setter expects AddressCollection
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('must be of type');

        $this->dataMapper->getHydrator()->hydrate(PersonWithNativeTypehint::class, $rawData);
    }

    /**
     * Test behavior equivalence between native typehint and explicit Property attribute
     */
    public function testBehaviorEquivalenceBetweenNativeAndExplicit(): void
    {
        $rawData = [
            'firstName' => 'George',
            'lastName' => 'Taylor',
            'addresses' => [
                'street' => '999 Maple Dr',
                'city' => 'Denver',
                'postalCode' => '80201',
            ],
        ];

        $personNative = $this->dataMapper->getHydrator()->hydrate(PersonWithNativeTypehint::class, $rawData);
        $personExplicit = $this->dataMapper->getHydrator()->hydrate(PersonWithExplicitProperty::class, $rawData);

        // Both should have the same structure
        $this->assertEquals($personNative->getFirstName(), $personExplicit->getFirstName());
        $this->assertEquals($personNative->getLastName(), $personExplicit->getLastName());
        
        $this->assertInstanceOf(AddressCollection::class, $personNative->getAddresses());
        $this->assertInstanceOf(AddressCollection::class, $personExplicit->getAddresses());
    }

    /**
     * Test missing property in raw data doesn't cause issues
     */
    public function testMissingPropertyInRawData(): void
    {
        $rawData = [
            'firstName' => 'Helen',
            'lastName' => 'Anderson',
            // addresses is missing
        ];

        $person = $this->dataMapper->getHydrator()->hydrate(PersonWithNativeTypehint::class, $rawData);

        $this->assertInstanceOf(PersonWithNativeTypehint::class, $person);
        $this->assertEquals('Helen', $person->getFirstName());
        $this->assertNull($person->getAddresses());
    }
}
