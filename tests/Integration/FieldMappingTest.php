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
use Kassko\DataMapper\Registry\LoaderRegistry;
use Kassko\Sample\PersonWithStore;
use PHPUnit\Framework\TestCase;

class FieldMappingTest extends TestCase
{
    protected function tearDown(): void
    {
        LoaderRegistry::clear();
    }

    public function testFieldAttributeMapsKeyToProperty(): void
    {
        new DataMapper(new \Kassko\DataMapper\ServiceResolver());
        $person = new PersonWithStore(1);

        // firstName property should use 'first_name' key from data
        $firstName = $person->getFirstName();
        $this->assertEquals('Foo', $firstName);
    }

    public function testFieldMappingWithMultipleIds(): void
    {
        new DataMapper(new \Kassko\DataMapper\ServiceResolver());
        
        $person1 = new PersonWithStore(1);
        $person2 = new PersonWithStore(2);
        $person3 = new PersonWithStore(3);

        $this->assertEquals('Foo', $person1->getFirstName());
        $this->assertEquals('Bar', $person2->getFirstName());
        $this->assertEquals('Baz', $person3->getFirstName());
    }

    public function testPropertiesWithoutFieldAttributeUsePropertyName(): void
    {
        new DataMapper(new \Kassko\DataMapper\ServiceResolver());
        $person = new PersonWithStore(1);

        // name and email properties don't have Field attribute
        // so they should use their property name as the key
        $this->assertEquals('foo', $person->getName());
        $this->assertEquals('foo@aaa.com', $person->getEmail());
    }
}
