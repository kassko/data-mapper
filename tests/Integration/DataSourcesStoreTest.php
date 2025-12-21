<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Tests\Integration;

use Kassko\DataMapper\DataMapper;
use Kassko\DataMapper\Registry\LazyLoaderRegistry;
use Kassko\Sample\PersonWithStore;
use PHPUnit\Framework\TestCase;

class DataSourcesStoreTest extends TestCase
{
    protected function tearDown(): void
    {
        LazyLoaderRegistry::clear();
    }

    public function testDataSourcesStoreWithSupplySeveralFields(): void
    {
        new DataMapper();
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
        new DataMapper();
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
        new DataMapper();
        $person = new PersonWithStore(3);

        // firstName property should be mapped to 'first_name' key in data
        $this->assertEquals('Baz', $person->getFirstName());
    }

    public function testPropertiesWithoutDataSourceRefAreNotHydrated(): void
    {
        new DataMapper();
        $person = new PersonWithStore(1);

        // Load some properties
        $person->getName();

        // Age has no DataSourceRef, should remain null even if key exists in data
        $this->assertNull($person->getAge());
    }
}
