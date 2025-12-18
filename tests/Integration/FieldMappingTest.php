<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Tests\Integration;

use Kassko\DataMapper\DataMapper;
use Kassko\Sample\PersonWithStore;
use PHPUnit\Framework\TestCase;

class FieldMappingTest extends TestCase
{
    public function testFieldAttributeMapsKeyToProperty(): void
    {
        $dataMapper = new DataMapper();
        $person = new PersonWithStore(1);

        $dataMapper->prepare($person);

        // firstName property should use 'first_name' key from data
        $firstName = $person->getFirstName();
        $this->assertEquals('Foo', $firstName);
    }

    public function testFieldMappingWithMultipleIds(): void
    {
        $dataMapper = new DataMapper();
        
        $person1 = new PersonWithStore(1);
        $person2 = new PersonWithStore(2);
        $person3 = new PersonWithStore(3);

        $dataMapper->prepare($person1);
        $dataMapper->prepare($person2);
        $dataMapper->prepare($person3);

        $this->assertEquals('Foo', $person1->getFirstName());
        $this->assertEquals('Bar', $person2->getFirstName());
        $this->assertEquals('Baz', $person3->getFirstName());
    }

    public function testPropertiesWithoutFieldAttributeUsePropertyName(): void
    {
        $dataMapper = new DataMapper();
        $person = new PersonWithStore(1);

        $dataMapper->prepare($person);

        // name and email properties don't have Field attribute
        // so they should use their property name as the key
        $this->assertEquals('foo', $person->getName());
        $this->assertEquals('foo@aaa.com', $person->getEmail());
    }
}
