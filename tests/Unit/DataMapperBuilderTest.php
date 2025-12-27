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

use Kassko\DataMapper\DataMapperBuilder;
use Kassko\DataMapper\DataMapper;
use Kassko\DataMapper\ArrayServiceLocator;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

final class DataMapperBuilderTest extends TestCase
{
    public function testBuildReturnsDataMapper(): void
    {
        $builder = new DataMapperBuilder();
        $dataMapper = $builder->build();

        $this->assertInstanceOf(DataMapper::class, $dataMapper);
    }

    public function testBuildWithContainer(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        
        $builder = new DataMapperBuilder();
        $dataMapper = $builder
            ->setContainer($container)
            ->build();

        $this->assertInstanceOf(DataMapper::class, $dataMapper);
    }

    public function testBuildWithLocators(): void
    {
        $locator1 = new ArrayServiceLocator(['key1' => 'value1']);
        $locator2 = new ArrayServiceLocator(['key2' => 'value2']);
        
        $builder = new DataMapperBuilder();
        $dataMapper = $builder
            ->addLocator($locator1)
            ->addLocator($locator2)
            ->build();

        $this->assertInstanceOf(DataMapper::class, $dataMapper);
    }

    public function testBuildWithContainerAndLocators(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $locator = new ArrayServiceLocator(['key1' => 'value1']);
        
        $builder = new DataMapperBuilder();
        $dataMapper = $builder
            ->setContainer($container)
            ->addLocator($locator)
            ->build();

        $this->assertInstanceOf(DataMapper::class, $dataMapper);
    }

    public function testBuilderIsReusable(): void
    {
        $builder = new DataMapperBuilder();
        
        $dataMapper1 = $builder->build();
        $dataMapper2 = $builder->build();

        $this->assertInstanceOf(DataMapper::class, $dataMapper1);
        $this->assertInstanceOf(DataMapper::class, $dataMapper2);
        $this->assertNotSame($dataMapper1, $dataMapper2);
    }

    public function testBuilderFluentInterface(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $locator1 = new ArrayServiceLocator(['key1' => 'value1']);
        $locator2 = new ArrayServiceLocator(['key2' => 'value2']);
        
        $builder = new DataMapperBuilder();
        
        // Test that methods return builder instance for chaining
        $result = $builder->setContainer($container);
        $this->assertSame($builder, $result);
        
        $result = $builder->addLocator($locator1);
        $this->assertSame($builder, $result);
        
        $result = $builder->addLocator($locator2);
        $this->assertSame($builder, $result);
    }

    public function testMultipleLocatorsCanBeAdded(): void
    {
        $locators = [
            new ArrayServiceLocator(['person' => '@person.service']),
            new ArrayServiceLocator(['car' => '@car.service']),
            new ArrayServiceLocator(['pet' => '@pet.service']),
        ];
        
        $builder = new DataMapperBuilder();
        
        foreach ($locators as $locator) {
            $builder->addLocator($locator);
        }
        
        $dataMapper = $builder->build();
        
        $this->assertInstanceOf(DataMapper::class, $dataMapper);
    }
}
