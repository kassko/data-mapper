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

class DataSourcesStoreTest extends TestCase
{
    protected function tearDown(): void
    {
        LoaderRegistry::clear();
    }

    public function testDataSourcesStoreWithSupplySeveralFields(): void
    {
        new DataMapper(new \Kassko\DataMapper\ServiceResolver());
        $person = new PersonWithStore(1);

        // Access firstName with Field mapping
        $this->assertEquals('Foo', $person->getFirstName());
        
        // Access name directly
        $this->assertEquals('foo', $person->getName());
        
        // Access email directly
        $this->assertEquals('foo@aaa.com', $person->getEmail());
        
        // Age should remain null (no DataSourceRef)
        $this->assertNull($person->getAge());
    }

    public function testSupplySeveralFieldsLoadsAllPropertiesInSingleCall(): void
    {
        new DataMapper(new \Kassko\DataMapper\ServiceResolver());
        $person = new PersonWithStore(2);

        // Trigger loading by accessing one property
        $name = $person->getName();
        $this->assertEquals('bar', $name);

        // Other properties with same DataSourceRef should already be loaded
        $this->assertEquals('Bar', $person->getFirstName());
        $this->assertEquals('bar@bbb.com', $person->getEmail());
    }

    public function testFieldAttributeMapsPropertyToDifferentKey(): void
    {
        new DataMapper(new \Kassko\DataMapper\ServiceResolver());
        $person = new PersonWithStore(3);

        // firstName property should be mapped to 'first_name' key in data
        $this->assertEquals('Baz', $person->getFirstName());
    }

    public function testPropertiesWithoutDataSourceRefAreNotHydrated(): void
    {
        new DataMapper(new \Kassko\DataMapper\ServiceResolver());
        $person = new PersonWithStore(1);

        // Load some properties
        $person->getName();

        // Age has no DataSourceRef, should remain null even if key exists in data
        $this->assertNull($person->getAge());
    }
}
