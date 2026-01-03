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

namespace Kassko\DataMapper\Tests\Integration\Portable;

use Kassko\DataMapper\Tests\TestHelpers\PortableIntegrationTestCase;
use Kassko\Sample\Portable\Person;

/**
 * Portable integration tests for lazy loading functionality.
 * 
 * These tests verify that lazy loading works correctly in both
 * native PHP and Symfony Bundle contexts.
 */
class LazyLoadingPortableTest extends PortableIntegrationTestCase
{
    public function testLazyLoadingWorks(): void
    {
        $this->getDataMapper();
        
        $person = new Person(1);
        
        $name = $person->getName();
        
        $this->assertEquals('foo', $name);
    }

    public function testLazyLoadingWithMultipleProperties(): void
    {
        $this->getDataMapper();
        
        $person = new Person(1);
        
        $this->assertEquals('foo', $person->getName());
        $this->assertEquals('foo@aaa.com', $person->getEmail());
    }

    public function testLazyLoadingWithDifferentIds(): void
    {
        $this->getDataMapper();
        
        $person1 = new Person(1);
        $person2 = new Person(2);
        $person3 = new Person(3);
        
        $this->assertEquals('foo', $person1->getName());
        $this->assertEquals('bar', $person2->getName());
        $this->assertEquals('baz', $person3->getName());
    }

    public function testObjectIsSerializable(): void
    {
        $this->getDataMapper();
        
        $person = new Person(1);
        
        // Object should be serializable (no service in properties)
        $serialized = serialize($person);
        $unserialized = unserialize($serialized);
        
        $this->assertInstanceOf(Person::class, $unserialized);
    }

    public function testSingleCallOptimization(): void
    {
        $this->getDataMapper();
        
        $person = new Person(1);
        
        // First call loads both properties
        $name = $person->getName();
        $this->assertEquals('foo', $name);
        
        // Second call uses cached data
        $email = $person->getEmail();
        $this->assertEquals('foo@aaa.com', $email);
    }
}
