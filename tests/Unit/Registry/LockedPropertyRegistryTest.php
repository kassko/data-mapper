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

use Kassko\DataMapper\Registry\LockedPropertyRegistry;
use PHPUnit\Framework\TestCase;

class LockedPropertyRegistryTest extends TestCase
{
    protected function tearDown(): void
    {
        LockedPropertyRegistry::clear();
    }

    public function testLockAndUnlockProperty(): void
    {
        $object = new class {
            public string $name = 'test';
        };

        // Initially not locked
        $this->assertFalse(LockedPropertyRegistry::isLocked($object, 'name'));

        // Lock the property
        LockedPropertyRegistry::lock($object, 'name');
        $this->assertTrue(LockedPropertyRegistry::isLocked($object, 'name'));

        // Unlock the property
        LockedPropertyRegistry::unlock($object, 'name');
        $this->assertFalse(LockedPropertyRegistry::isLocked($object, 'name'));
    }

    public function testGetLockedProperties(): void
    {
        $object = new class {
            public string $name = 'test';
            public string $email = 'test@test.com';
        };

        LockedPropertyRegistry::lock($object, 'name');
        LockedPropertyRegistry::lock($object, 'email');

        $locked = LockedPropertyRegistry::getLockedProperties($object);
        
        $this->assertArrayHasKey('name', $locked);
        $this->assertArrayHasKey('email', $locked);
        $this->assertTrue($locked['name']);
        $this->assertTrue($locked['email']);
    }

    public function testClearObject(): void
    {
        $object = new class {
            public string $name = 'test';
        };

        LockedPropertyRegistry::lock($object, 'name');
        $this->assertTrue(LockedPropertyRegistry::isLocked($object, 'name'));

        LockedPropertyRegistry::clearObject($object);
        $this->assertFalse(LockedPropertyRegistry::isLocked($object, 'name'));
    }

    public function testClearAll(): void
    {
        $object1 = new class {
            public string $name = 'test1';
        };
        $object2 = new class {
            public string $name = 'test2';
        };

        LockedPropertyRegistry::lock($object1, 'name');
        LockedPropertyRegistry::lock($object2, 'name');

        LockedPropertyRegistry::clear();

        $this->assertFalse(LockedPropertyRegistry::isLocked($object1, 'name'));
        $this->assertFalse(LockedPropertyRegistry::isLocked($object2, 'name'));
    }

    public function testDifferentObjectsHaveSeparateLocks(): void
    {
        $object1 = new class {
            public string $name = 'test1';
        };
        $object2 = new class {
            public string $name = 'test2';
        };

        LockedPropertyRegistry::lock($object1, 'name');

        $this->assertTrue(LockedPropertyRegistry::isLocked($object1, 'name'));
        $this->assertFalse(LockedPropertyRegistry::isLocked($object2, 'name'));
    }
}
