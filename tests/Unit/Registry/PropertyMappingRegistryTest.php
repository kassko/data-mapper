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

namespace Kassko\DataMapper\Tests\Unit\Registry;

use Kassko\DataMapper\Registry\PropertyMappingRegistry;
use PHPUnit\Framework\TestCase;

class PropertyMappingRegistryTest extends TestCase
{
    protected function tearDown(): void
    {
        PropertyMappingRegistry::reset();
    }

    public function testRegisterAndGetSourceField(): void
    {
        $object = new class {
            public string $firstName = '';
        };

        PropertyMappingRegistry::register($object, 'firstName', 'first_name');

        $this->assertEquals('first_name', PropertyMappingRegistry::getSourceField($object, 'firstName'));
    }

    public function testRegisterAndGetPropertyName(): void
    {
        $object = new class {
            public string $firstName = '';
        };

        PropertyMappingRegistry::register($object, 'firstName', 'first_name');

        $this->assertEquals('firstName', PropertyMappingRegistry::getPropertyName($object, 'first_name'));
    }

    public function testBidirectionalMapping(): void
    {
        $object = new class {
            public string $firstName = '';
            public string $lastName = '';
            public string $email = '';
        };

        // Register multiple mappings
        PropertyMappingRegistry::register($object, 'firstName', 'first_name');
        PropertyMappingRegistry::register($object, 'lastName', 'last_name');
        PropertyMappingRegistry::register($object, 'email', 'email'); // Same name

        // Test property -> sourceField direction
        $this->assertEquals('first_name', PropertyMappingRegistry::getSourceField($object, 'firstName'));
        $this->assertEquals('last_name', PropertyMappingRegistry::getSourceField($object, 'lastName'));
        $this->assertEquals('email', PropertyMappingRegistry::getSourceField($object, 'email'));

        // Test sourceField -> property direction
        $this->assertEquals('firstName', PropertyMappingRegistry::getPropertyName($object, 'first_name'));
        $this->assertEquals('lastName', PropertyMappingRegistry::getPropertyName($object, 'last_name'));
        $this->assertEquals('email', PropertyMappingRegistry::getPropertyName($object, 'email'));
    }

    public function testGetSourceFieldReturnsNullForUnregistered(): void
    {
        $object = new class {
            public string $name = '';
        };

        $this->assertNull(PropertyMappingRegistry::getSourceField($object, 'name'));
    }

    public function testGetPropertyNameReturnsNullForUnregistered(): void
    {
        $object = new class {
            public string $name = '';
        };

        $this->assertNull(PropertyMappingRegistry::getPropertyName($object, 'unknown_field'));
    }

    public function testHasPropertyMapping(): void
    {
        $object = new class {
            public string $firstName = '';
        };

        $this->assertFalse(PropertyMappingRegistry::hasPropertyMapping($object, 'firstName'));

        PropertyMappingRegistry::register($object, 'firstName', 'first_name');

        $this->assertTrue(PropertyMappingRegistry::hasPropertyMapping($object, 'firstName'));
        $this->assertFalse(PropertyMappingRegistry::hasPropertyMapping($object, 'lastName'));
    }

    public function testHasSourceFieldMapping(): void
    {
        $object = new class {
            public string $firstName = '';
        };

        $this->assertFalse(PropertyMappingRegistry::hasSourceFieldMapping($object, 'first_name'));

        PropertyMappingRegistry::register($object, 'firstName', 'first_name');

        $this->assertTrue(PropertyMappingRegistry::hasSourceFieldMapping($object, 'first_name'));
        $this->assertFalse(PropertyMappingRegistry::hasSourceFieldMapping($object, 'last_name'));
    }

    public function testGetAllMappings(): void
    {
        $object = new class {
            public string $firstName = '';
            public string $lastName = '';
        };

        PropertyMappingRegistry::register($object, 'firstName', 'first_name');
        PropertyMappingRegistry::register($object, 'lastName', 'last_name');

        $mappings = PropertyMappingRegistry::getAllMappings($object);

        $this->assertCount(2, $mappings);
        $this->assertEquals('first_name', $mappings['firstName']);
        $this->assertEquals('last_name', $mappings['lastName']);
    }

    public function testGetAllReverseMappings(): void
    {
        $object = new class {
            public string $firstName = '';
            public string $lastName = '';
        };

        PropertyMappingRegistry::register($object, 'firstName', 'first_name');
        PropertyMappingRegistry::register($object, 'lastName', 'last_name');

        $reverseMappings = PropertyMappingRegistry::getAllReverseMappings($object);

        $this->assertCount(2, $reverseMappings);
        $this->assertEquals('firstName', $reverseMappings['first_name']);
        $this->assertEquals('lastName', $reverseMappings['last_name']);
    }

    public function testClearObject(): void
    {
        $object = new class {
            public string $firstName = '';
        };

        PropertyMappingRegistry::register($object, 'firstName', 'first_name');
        $this->assertTrue(PropertyMappingRegistry::hasPropertyMapping($object, 'firstName'));

        PropertyMappingRegistry::clear($object);

        $this->assertFalse(PropertyMappingRegistry::hasPropertyMapping($object, 'firstName'));
    }

    public function testResetClearsAllMappings(): void
    {
        $object1 = new class {
            public string $name = '';
        };
        $object2 = new class {
            public string $name = '';
        };

        PropertyMappingRegistry::register($object1, 'name', 'name_field');
        PropertyMappingRegistry::register($object2, 'name', 'different_field');

        PropertyMappingRegistry::reset();

        $this->assertFalse(PropertyMappingRegistry::hasPropertyMapping($object1, 'name'));
        $this->assertFalse(PropertyMappingRegistry::hasPropertyMapping($object2, 'name'));
    }

    public function testDifferentObjectsHaveSeparateMappings(): void
    {
        $object1 = new class {
            public string $name = '';
        };
        $object2 = new class {
            public string $name = '';
        };

        PropertyMappingRegistry::register($object1, 'name', 'field_1');
        PropertyMappingRegistry::register($object2, 'name', 'field_2');

        $this->assertEquals('field_1', PropertyMappingRegistry::getSourceField($object1, 'name'));
        $this->assertEquals('field_2', PropertyMappingRegistry::getSourceField($object2, 'name'));
    }

    public function testGetAllMappingsReturnsEmptyArrayForUnregisteredObject(): void
    {
        $object = new class {
            public string $name = '';
        };

        $this->assertEquals([], PropertyMappingRegistry::getAllMappings($object));
        $this->assertEquals([], PropertyMappingRegistry::getAllReverseMappings($object));
    }

    public function testOverwriteExistingMapping(): void
    {
        $object = new class {
            public string $name = '';
        };

        PropertyMappingRegistry::register($object, 'name', 'old_field');
        PropertyMappingRegistry::register($object, 'name', 'new_field');

        $this->assertEquals('new_field', PropertyMappingRegistry::getSourceField($object, 'name'));
        $this->assertEquals('name', PropertyMappingRegistry::getPropertyName($object, 'new_field'));
        // Old mapping should still exist in reverse direction (WeakMap behavior)
        $this->assertEquals('name', PropertyMappingRegistry::getPropertyName($object, 'old_field'));
    }
}
