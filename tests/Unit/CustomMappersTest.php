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
use PHPUnit\Framework\TestCase;

final class CustomMappersTest extends TestCase
{
    public function testGetCustomHydratorReturnsRegisteredHydrator(): void
    {
        $customHydrator = fn(array $data, object $context) => 'hydrated value';
        
        $builder = new DataMapperBuilder();
        $builder->addCustomHydrator('my_hydrator', $customHydrator);
        $dataMapper = $builder->build();

        $result = $dataMapper->getCustomHydrator('my_hydrator');

        $this->assertSame($customHydrator, $result);
    }

    public function testGetCustomHydratorReturnsNullForUnregistered(): void
    {
        $builder = new DataMapperBuilder();
        $dataMapper = $builder->build();

        $result = $dataMapper->getCustomHydrator('nonexistent');

        $this->assertNull($result);
    }

    public function testGetCustomObjectMapperReturnsRegisteredMapper(): void
    {
        $customMapper = fn(object $source, object $context) => new \stdClass();
        
        $builder = new DataMapperBuilder();
        $builder->addCustomObjectMapper('address_mapper', $customMapper);
        $dataMapper = $builder->build();

        $result = $dataMapper->getCustomObjectMapper('address_mapper');

        $this->assertSame($customMapper, $result);
    }

    public function testGetCustomObjectMapperReturnsNullForUnregistered(): void
    {
        $builder = new DataMapperBuilder();
        $dataMapper = $builder->build();

        $result = $dataMapper->getCustomObjectMapper('nonexistent');

        $this->assertNull($result);
    }

    public function testMultipleCustomHydratorsRegistered(): void
    {
        $hydrator1 = fn(array $data, object $context) => 'value1';
        $hydrator2 = fn(array $data, object $context) => 'value2';
        
        $builder = new DataMapperBuilder();
        $builder->addCustomHydrator('hydrator1', $hydrator1);
        $builder->addCustomHydrator('hydrator2', $hydrator2);
        $dataMapper = $builder->build();

        $this->assertSame($hydrator1, $dataMapper->getCustomHydrator('hydrator1'));
        $this->assertSame($hydrator2, $dataMapper->getCustomHydrator('hydrator2'));
    }

    public function testMultipleCustomObjectMappersRegistered(): void
    {
        $mapper1 = fn(object $source, object $context) => new \stdClass();
        $mapper2 = fn(object $source, object $context) => new \stdClass();
        
        $builder = new DataMapperBuilder();
        $builder->addCustomObjectMapper('mapper1', $mapper1);
        $builder->addCustomObjectMapper('mapper2', $mapper2);
        $dataMapper = $builder->build();

        $this->assertSame($mapper1, $dataMapper->getCustomObjectMapper('mapper1'));
        $this->assertSame($mapper2, $dataMapper->getCustomObjectMapper('mapper2'));
    }

    public function testAddCustomHydratorReturnsBuilderForChaining(): void
    {
        $builder = new DataMapperBuilder();
        
        $result = $builder->addCustomHydrator('test', fn() => 'value');
        
        $this->assertSame($builder, $result);
    }

    public function testAddCustomObjectMapperReturnsBuilderForChaining(): void
    {
        $builder = new DataMapperBuilder();
        
        $result = $builder->addCustomObjectMapper('test', fn() => new \stdClass());
        
        $this->assertSame($builder, $result);
    }
}
