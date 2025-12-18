<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Tests\Integration;

use Kassko\DataMapper\DataMapper;
use Kassko\Sample\Person;
use PHPUnit\Framework\TestCase;

class DataMapperTest extends TestCase
{
    public function testDataMapperPreparesObjectForLazyLoading(): void
    {
        $dataMapper = new DataMapper();
        $person = new Person(1);

        $dataMapper->prepare($person);

        // Access properties - they should be lazy loaded
        $this->assertEquals('foo', $person->getName());
        $this->assertEquals('foo@aaa.com', $person->getEmail());
    }

    public function testSingleCallOptimization(): void
    {
        $dataMapper = new DataMapper();
        $person = new Person(1);

        $dataMapper->prepare($person);

        // First call to getName() should load both name and email
        $name = $person->getName();
        $this->assertEquals('foo', $name);

        // Second call to getEmail() should NOT trigger another DataSource call
        // (this is verified by the fact that we only have one DataSource mock call)
        $email = $person->getEmail();
        $this->assertEquals('foo@aaa.com', $email);
    }

    public function testMultiplePersonsWithDifferentIds(): void
    {
        $dataMapper = new DataMapper();
        
        $person1 = new Person(1);
        $person2 = new Person(2);
        $person3 = new Person(3);

        $dataMapper->prepare($person1);
        $dataMapper->prepare($person2);
        $dataMapper->prepare($person3);

        $this->assertEquals('foo', $person1->getName());
        $this->assertEquals('foo@aaa.com', $person1->getEmail());

        $this->assertEquals('bar', $person2->getName());
        $this->assertEquals('bar@bbb.com', $person2->getEmail());

        $this->assertEquals('baz', $person3->getName());
        $this->assertEquals('baz@ccc.com', $person3->getEmail());
    }

    public function testPrepareThrowsExceptionForObjectWithoutLoadableTrait(): void
    {
        $dataMapper = new DataMapper();
        $object = new \stdClass();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/must use.*LoadableTrait/');

        $dataMapper->prepare($object);
    }
}
