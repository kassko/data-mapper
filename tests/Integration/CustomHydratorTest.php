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

use Kassko\DataMapper\DataMapper;
use Kassko\DataMapper\DataMapperBuilder;
use Kassko\DataMapper\Registry\LoaderRegistry;
use Kassko\Sample\PersonCustomHydrator;
use Kassko\Sample\Address;
use PHPUnit\Framework\TestCase;

class CustomHydratorTest extends TestCase
{
    protected function tearDown(): void
    {
        LoaderRegistry::clear();
    }

    public function testCustomHydratorWithObjectClass(): void
    {
        $dataMapper = (new DataMapperBuilder())
            ->addCustomHydrator('address_hydrator', function (array $data): Address {
                return new Address(
                    street: '123 Main Street',
                    city: 'Paris',
                    zipCode: '75001'
                );
            })
            ->addCustomHydrator('phone_hydrator', function (array $data): string {
                return '+33 1 23 45 67 89';
            })
            ->build();

        $person = new PersonCustomHydrator(1);

        $address = $person->getAddress();
        
        $this->assertInstanceOf(Address::class, $address);
        $this->assertEquals('123 Main Street', $address->street);
        $this->assertEquals('Paris', $address->city);
        $this->assertEquals('75001', $address->zipCode);
        $this->assertEquals('France', $address->country);
    }

    public function testCustomHydratorWithSimpleType(): void
    {
        $dataMapper = (new DataMapperBuilder())
            ->addCustomHydrator('address_hydrator', function (array $data): Address {
                return new Address(
                    street: '123 Main Street',
                    city: 'Paris',
                    zipCode: '75001'
                );
            })
            ->addCustomHydrator('phone_hydrator', function (array $data): string {
                return '+33 1 23 45 67 89';
            })
            ->build();

        $person = new PersonCustomHydrator(1);

        $phone = $person->getPhone();
        
        $this->assertEquals('+33 1 23 45 67 89', $phone);
    }

    public function testCustomHydratorNotRegisteredThrowsException(): void
    {
        $dataMapper = (new DataMapperBuilder())
            // Only register phone_hydrator, not address_hydrator
            ->addCustomHydrator('phone_hydrator', function (array $data): string {
                return '+33 1 23 45 67 89';
            })
            ->build();

        $person = new PersonCustomHydrator(1);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Custom hydrator with key "address_hydrator" not found');
        
        $person->getAddress();
    }

    public function testCustomHydratorWrongObjectClassThrowsException(): void
    {
        $dataMapper = (new DataMapperBuilder())
            ->addCustomHydrator('address_hydrator', function (array $data): object {
                // Return wrong type - should throw because PersonCustomHydrator expects Address::class
                return new \stdClass();
            })
            ->addCustomHydrator('phone_hydrator', function (array $data): string {
                return '+33 1 23 45 67 89';
            })
            ->build();

        $person = new PersonCustomHydrator(1);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Custom hydrator "address_hydrator" returned object of type stdClass, but expected Kassko\\Sample\\Address');
        
        $person->getAddress();
    }

    public function testCustomHydratorCanReturnNull(): void
    {
        $dataMapper = (new DataMapperBuilder())
            ->addCustomHydrator('address_hydrator', function (array $data): ?Address {
                return null; // Null is always valid regardless of objectClass
            })
            ->addCustomHydrator('phone_hydrator', function (array $data): ?string {
                return null;
            })
            ->build();

        $person = new PersonCustomHydrator(1);

        $address = $person->getAddress();
        
        $this->assertNull($address);
    }

    public function testCustomHydratorWithMultipleInstances(): void
    {
        $callCount = 0;
        
        $dataMapper = (new DataMapperBuilder())
            ->addCustomHydrator('address_hydrator', function (array $data) use (&$callCount): Address {
                $callCount++;
                return new Address(
                    street: 'Street ' . $callCount,
                    city: 'City ' . $callCount,
                    zipCode: '7500' . $callCount
                );
            })
            ->addCustomHydrator('phone_hydrator', function (array $data): string {
                return '+33 1 23 45 67 89';
            })
            ->build();

        $person1 = new PersonCustomHydrator(1);
        $person2 = new PersonCustomHydrator(2);

        // Each person should get their own address
        $address1 = $person1->getAddress();
        $address2 = $person2->getAddress();
        
        $this->assertEquals('Street 1', $address1->street);
        $this->assertEquals('Street 2', $address2->street);
        $this->assertEquals(2, $callCount);
    }

    public function testCustomHydratorPropertyLoadedOnlyOnce(): void
    {
        $callCount = 0;
        
        $dataMapper = (new DataMapperBuilder())
            ->addCustomHydrator('address_hydrator', function (array $data) use (&$callCount): Address {
                $callCount++;
                return new Address(
                    street: '123 Main Street',
                    city: 'Paris',
                    zipCode: '75001'
                );
            })
            ->addCustomHydrator('phone_hydrator', function (array $data): string {
                return '+33 1 23 45 67 89';
            })
            ->build();

        $person = new PersonCustomHydrator(1);

        // Call getAddress multiple times
        $address1 = $person->getAddress();
        $address2 = $person->getAddress();
        $address3 = $person->getAddress();
        
        // Hydrator should only be called once
        $this->assertEquals(1, $callCount);
        $this->assertSame($address1, $address2);
        $this->assertSame($address2, $address3);
    }
}
