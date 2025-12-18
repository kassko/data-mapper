<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Tests\Unit;

use Kassko\DataMapper\Attribute\DataSource;
use Kassko\DataMapper\Metadata\AttributeReader;
use Kassko\Sample\Person;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class AttributeReaderTest extends TestCase
{
    private AttributeReader $reader;

    protected function setUp(): void
    {
        $this->reader = new AttributeReader();
    }

    public function testReadDataSourceFromProperty(): void
    {
        $person = new Person(1);
        $reflectionClass = new ReflectionClass($person);
        $property = $reflectionClass->getProperty('name');

        $dataSource = $this->reader->readDataSource($property);

        $this->assertInstanceOf(DataSource::class, $dataSource);
        $this->assertStringContainsString('PersonDataSource', $dataSource->class);
        $this->assertEquals('getData', $dataSource->method);
        $this->assertEquals(['#id'], $dataSource->args);
    }

    public function testReadDataSourceReturnsNullForPropertyWithoutAttribute(): void
    {
        $person = new Person(1);
        $reflectionClass = new ReflectionClass($person);
        $property = $reflectionClass->getProperty('id');

        $dataSource = $this->reader->readDataSource($property);

        $this->assertNull($dataSource);
    }

    public function testGetPropertiesWithDataSource(): void
    {
        $person = new Person(1);
        $properties = $this->reader->getPropertiesWithDataSource($person);

        $this->assertCount(2, $properties);
        $this->assertArrayHasKey('name', $properties);
        $this->assertArrayHasKey('email', $properties);
        $this->assertInstanceOf(DataSource::class, $properties['name']);
        $this->assertInstanceOf(DataSource::class, $properties['email']);
    }
}
