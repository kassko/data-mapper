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

namespace Kassko\DataMapper\Tests\Unit;

use Kassko\DataMapper\DataMapper;
use Kassko\DataMapper\DataMapperBuilder;
use Kassko\DataMapper\Hydrator;
use Kassko\Sample\SimpleHydratable;
use Kassko\Sample\HydratableWithHooks;
use PHPUnit\Framework\TestCase;

final class HydratorTest extends TestCase
{
    private DataMapper $dataMapper;
    private Hydrator $hydrator;

    protected function setUp(): void
    {
        $builder = new DataMapperBuilder();
        $this->dataMapper = $builder->build();
        $this->hydrator = $this->dataMapper->getHydrator();
    }

    public function testGetHydratorReturnsSameInstance(): void
    {
        $hydrator1 = $this->dataMapper->getHydrator();
        $hydrator2 = $this->dataMapper->getHydrator();

        $this->assertSame($hydrator1, $hydrator2);
    }

    public function testGetHydratorReturnsHydratorInstance(): void
    {
        $this->assertInstanceOf(Hydrator::class, $this->hydrator);
    }

    public function testHydrateCreatesObjectFromClassName(): void
    {
        $rawData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'age' => 30,
        ];

        $object = $this->hydrator->hydrate(SimpleHydratable::class, $rawData);

        $this->assertInstanceOf(SimpleHydratable::class, $object);
        $this->assertEquals('John', $object->getFirstName());
        $this->assertEquals('Doe', $object->getLastName());
        $this->assertEquals('john@example.com', $object->getEmail());
        $this->assertEquals(30, $object->getAge());
    }

    public function testHydrateWithPropertyNameMapping(): void
    {
        // first_name and last_name are mapped using #[Property] attribute
        $rawData = [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
        ];

        $object = $this->hydrator->hydrate(SimpleHydratable::class, $rawData);

        $this->assertEquals('Jane', $object->getFirstName());
        $this->assertEquals('Smith', $object->getLastName());
    }

    public function testHydrateWithMissingFields(): void
    {
        // Only provide some fields
        $rawData = [
            'first_name' => 'Alice',
        ];

        $object = $this->hydrator->hydrate(SimpleHydratable::class, $rawData);

        $this->assertEquals('Alice', $object->getFirstName());
        $this->assertNull($object->getLastName());
        $this->assertNull($object->getEmail());
        $this->assertNull($object->getAge());
    }

    public function testHydrateWithEmptyData(): void
    {
        $object = $this->hydrator->hydrate(SimpleHydratable::class, []);

        $this->assertInstanceOf(SimpleHydratable::class, $object);
        $this->assertNull($object->getFirstName());
        $this->assertNull($object->getLastName());
    }

    public function testHydrateExistingObject(): void
    {
        $existing = new SimpleHydratable();
        $existing->setFirstName('Original');

        $rawData = [
            'last_name' => 'NewLast',
            'email' => 'new@example.com',
        ];

        $result = $this->hydrator->hydrateExisting($existing, $rawData);

        $this->assertSame($existing, $result);
        // Original firstName should remain since it's not in rawData
        $this->assertEquals('Original', $result->getFirstName());
        $this->assertEquals('NewLast', $result->getLastName());
        $this->assertEquals('new@example.com', $result->getEmail());
    }

    public function testHydrateWithHooks(): void
    {
        $rawData = [
            'first_name' => 'John',
            'lastName' => 'Doe',
        ];

        $object = $this->hydrator->hydrate(HydratableWithHooks::class, $rawData);

        $this->assertInstanceOf(HydratableWithHooks::class, $object);
        // The object should have its firstName set
        $this->assertEquals('John', $object->getFirstName());
        // Hooks should have been executed
        $this->assertTrue($object->isInitialized());
        $this->assertTrue($object->isBeforeSetNameCalled());
        $this->assertTrue($object->isAfterSetNameCalled());
    }

    public function testHydrateReturnsCorrectType(): void
    {
        $rawData = ['first_name' => 'Test'];

        $object = $this->hydrator->hydrate(SimpleHydratable::class, $rawData);

        // Verify the object is of the expected type
        $this->assertInstanceOf(SimpleHydratable::class, $object);
    }

    public function testMultipleHydrations(): void
    {
        $data1 = ['first_name' => 'First', 'email' => 'first@example.com'];
        $data2 = ['first_name' => 'Second', 'email' => 'second@example.com'];

        $object1 = $this->hydrator->hydrate(SimpleHydratable::class, $data1);
        $object2 = $this->hydrator->hydrate(SimpleHydratable::class, $data2);

        $this->assertNotSame($object1, $object2);
        $this->assertEquals('First', $object1->getFirstName());
        $this->assertEquals('Second', $object2->getFirstName());
        $this->assertEquals('first@example.com', $object1->getEmail());
        $this->assertEquals('second@example.com', $object2->getEmail());
    }
}
