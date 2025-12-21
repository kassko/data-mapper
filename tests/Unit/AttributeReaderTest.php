<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Tests\Unit;

use Kassko\DataMapper\Attribute\DataSource;
use Kassko\DataMapper\Attribute\SinglePropDataSource;
use Kassko\DataMapper\Attribute\MultiPropDataSource;
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

    public function testReadSinglePropDataSourceFromProperty(): void
    {
        // Create a test class with SinglePropDataSource
        $testObject = new #[DataSource(class: 'TestSource', method: 'getData', args: ['#id'])] class {
            #[DataSource(class: 'TestSource', method: 'getData', args: ['#id'])]
            private ?string $name = null;
        };
        
        $reflectionClass = new ReflectionClass($testObject);
        $property = $reflectionClass->getProperty('name');

        $dataSource = $this->reader->readSinglePropDataSource($property);

        $this->assertInstanceOf(DataSource::class, $dataSource);
        $this->assertEquals('TestSource', $dataSource->class);
        $this->assertEquals('getData', $dataSource->method);
        $this->assertEquals(['#id'], $dataSource->args);
    }

    public function testReadDataSourceReturnsNullForPropertyWithoutAttribute(): void
    {
        $person = new Person(1);
        $reflectionClass = new ReflectionClass($person);
        $property = $reflectionClass->getProperty('id');

        $dataSource = $this->reader->readSinglePropDataSource($property);

        $this->assertNull($dataSource);
    }

    public function testReadMultiPropDataSourcesFromClass(): void
    {
        $person = new Person(1);
        $reflectionClass = new ReflectionClass($person);
        
        $dataSources = $this->reader->readMultiPropDataSources($reflectionClass);

        $this->assertCount(1, $dataSources);
        $this->assertInstanceOf(MultiPropDataSource::class, $dataSources[0]);
        $this->assertEquals('personData', $dataSources[0]->id);
    }
}
