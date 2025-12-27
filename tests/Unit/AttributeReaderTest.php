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
        
        // MultiPropDataSource is now inside DataSourcesStore
        $dataSourcesStore = $this->reader->readDataSourcesStore($reflectionClass);
        
        $this->assertNotNull($dataSourcesStore);
        $this->assertCount(1, $dataSourcesStore->sources);
        $this->assertInstanceOf(MultiPropDataSource::class, $dataSourcesStore->sources[0]);
        $this->assertEquals('personData', $dataSourcesStore->sources[0]->id);
    }
}
