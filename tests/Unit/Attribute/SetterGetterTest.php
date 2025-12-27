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

namespace Kassko\DataMapper\Tests\Unit\Attribute;

use Kassko\DataMapper\Attribute\Getter;
use Kassko\DataMapper\Attribute\Setter;
use Kassko\DataMapper\Metadata\AttributeReader;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class SetterGetterTest extends TestCase
{
    private AttributeReader $reader;

    protected function setUp(): void
    {
        $this->reader = new AttributeReader();
    }

    public function testSetterAttributeExists(): void
    {
        $testClass = new class {
            #[Setter(name: 'setCustomName')]
            private ?string $name = null;
        };

        $reflection = new ReflectionClass($testClass);
        $property = $reflection->getProperty('name');

        $setter = $this->reader->readSetter($property);

        $this->assertInstanceOf(Setter::class, $setter);
        $this->assertEquals('setCustomName', $setter->name);
        $this->assertEquals(Setter::TYPE_SETTER, $setter->type);
    }

    public function testSetterAttributeWithAdderType(): void
    {
        $testClass = new class {
            #[Setter(name: 'addItem', type: Setter::TYPE_ADDER)]
            private array $items = [];
        };

        $reflection = new ReflectionClass($testClass);
        $property = $reflection->getProperty('items');

        $setter = $this->reader->readSetter($property);

        $this->assertInstanceOf(Setter::class, $setter);
        $this->assertEquals('addItem', $setter->name);
        $this->assertEquals(Setter::TYPE_ADDER, $setter->type);
    }

    public function testSetterAttributeNotPresentReturnsNull(): void
    {
        $testClass = new class {
            private ?string $name = null;
        };

        $reflection = new ReflectionClass($testClass);
        $property = $reflection->getProperty('name');

        $setter = $this->reader->readSetter($property);

        $this->assertNull($setter);
    }

    public function testGetterAttributeExists(): void
    {
        $testClass = new class {
            #[Getter(name: 'getCustomName')]
            private ?string $name = null;
        };

        $reflection = new ReflectionClass($testClass);
        $property = $reflection->getProperty('name');

        $getter = $this->reader->readGetter($property);

        $this->assertInstanceOf(Getter::class, $getter);
        $this->assertEquals('getCustomName', $getter->name);
        $this->assertEquals(Getter::TYPE_GETTER, $getter->type);
    }

    public function testGetterAttributeWithIsserType(): void
    {
        $testClass = new class {
            #[Getter(name: 'isActive', type: Getter::TYPE_ISSER)]
            private bool $active = false;
        };

        $reflection = new ReflectionClass($testClass);
        $property = $reflection->getProperty('active');

        $getter = $this->reader->readGetter($property);

        $this->assertInstanceOf(Getter::class, $getter);
        $this->assertEquals('isActive', $getter->name);
        $this->assertEquals(Getter::TYPE_ISSER, $getter->type);
    }

    public function testGetterAttributeWithHaserType(): void
    {
        $testClass = new class {
            #[Getter(name: 'hasChildren', type: Getter::TYPE_HASER)]
            private bool $children = false;
        };

        $reflection = new ReflectionClass($testClass);
        $property = $reflection->getProperty('children');

        $getter = $this->reader->readGetter($property);

        $this->assertInstanceOf(Getter::class, $getter);
        $this->assertEquals('hasChildren', $getter->name);
        $this->assertEquals(Getter::TYPE_HASER, $getter->type);
    }

    public function testGetterAttributeNotPresentReturnsNull(): void
    {
        $testClass = new class {
            private ?string $name = null;
        };

        $reflection = new ReflectionClass($testClass);
        $property = $reflection->getProperty('name');

        $getter = $this->reader->readGetter($property);

        $this->assertNull($getter);
    }
}
