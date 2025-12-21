<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Tests\Integration;

use Kassko\DataMapper\DataMapper;
use Kassko\DataMapper\Registry\LazyLoaderRegistry;
use Kassko\Sample\PersonWithStore;
use PHPUnit\Framework\TestCase;

class FieldMappingTest extends TestCase
{
    protected function tearDown(): void
    {
        LazyLoaderRegistry::clear();
    }

    public function testFieldAttributeMapsKeyToProperty(): void
    {
        new DataMapper();
        $person = new PersonWithStore(1);

        // firstName property should use 'first_name' key from data
        $firstName = $person->getFirstName();
        $this->assertEquals('Foo', $firstName);
    }

    public function testFieldMappingWithMultipleIds(): void
    {
        new DataMapper();
        
        $person1 = new PersonWithStore(1);
        $person2 = new PersonWithStore(2);
        $person3 = new PersonWithStore(3);

        $this->assertEquals('Foo', $person1->getFirstName());
        $this->assertEquals('Bar', $person2->getFirstName());
        $this->assertEquals('Baz', $person3->getFirstName());
    }

    public function testPropertiesWithoutFieldAttributeUsePropertyName(): void
    {
        new DataMapper();
        $person = new PersonWithStore(1);

        // name and email properties don't have Field attribute
        // so they should use their property name as the key
        $this->assertEquals('foo', $person->getName());
        $this->assertEquals('foo@aaa.com', $person->getEmail());
    }
}
