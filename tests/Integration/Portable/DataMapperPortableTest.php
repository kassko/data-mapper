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

namespace Kassko\DataMapper\Tests\Integration\Portable;

use Kassko\DataMapper\Tests\TestHelpers\PortableIntegrationTestCase;

/**
 * Portable integration tests for basic DataMapper functionality.
 * 
 * These tests verify core DataMapper behavior and can be run in both
 * native PHP and Symfony Bundle contexts.
 */
class DataMapperPortableTest extends PortableIntegrationTestCase
{
    public function testDataMapperCanBeObtained(): void
    {
        $dataMapper = $this->getDataMapper();
        
        $this->assertNotNull($dataMapper);
    }

    public function testDataMapperHasHydrator(): void
    {
        $dataMapper = $this->getDataMapper();
        
        $hydrator = $dataMapper->getHydrator();
        
        $this->assertNotNull($hydrator);
    }

    public function testDataMapperHasServiceResolver(): void
    {
        $dataMapper = $this->getDataMapper();
        
        $serviceResolver = $dataMapper->getServiceResolver();
        
        $this->assertNotNull($serviceResolver);
    }

    public function testDataMapperHasLineageCollector(): void
    {
        $dataMapper = $this->getDataMapper();
        
        $lineageCollector = $dataMapper->getLineageCollector();
        
        $this->assertNotNull($lineageCollector);
    }

    public function testLineageCollectorCanBeEnabled(): void
    {
        $dataMapper = $this->getDataMapper();
        
        // By default, lineage collector is disabled
        $this->assertFalse($dataMapper->getLineageCollector()->isEnabled());
        
        // Enable it manually
        $dataMapper->getLineageCollector()->enable();
        
        $this->assertTrue($dataMapper->getLineageCollector()->isEnabled());
    }

    public function testContextManagement(): void
    {
        $dataMapper = $this->getDataMapper();
        
        // Add context value
        $dataMapper->addToContext('test_key', 'test_value');
        
        // Verify retrieval
        $this->assertTrue($dataMapper->hasContext('test_key'));
        $this->assertEquals('test_value', $dataMapper->getContext('test_key'));
        
        // Clear and verify
        $dataMapper->clearContext();
        $this->assertFalse($dataMapper->hasContext('test_key'));
    }

    public function testAddManyToContext(): void
    {
        $dataMapper = $this->getDataMapper();
        
        $dataMapper->addManyToContext([
            'key1' => 'value1',
            'key2' => 'value2',
        ]);
        
        $this->assertEquals('value1', $dataMapper->getContext('key1'));
        $this->assertEquals('value2', $dataMapper->getContext('key2'));
    }
}
