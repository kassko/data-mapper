<?php

declare(strict_types=1);

/*
 * This file is part of DataMapper.
 *
 * Copyright 2025 kassko 
 *
 * For the full copyright and license information,
 * please view the LICENSE and NOTICE files that were distributed with this source code.
 */

namespace Kassko\DataMapper\Tests\Integration\Features\AttributeEnablement;

use Kassko\DataMapper\Hydrator;
use Kassko\DataMapper\DataMapperBuilder;
use Kassko\DataMapper\ArrayServiceLocator;
use Kassko\DataMapper\Metadata\AttributeReader;
use Kassko\DataMapper\Tests\TestHelpers\LocalFixtureAutoloadTrait;
use Kassko\Sample\AttributeEnablement\EnabledDisabledEntity;
use Kassko\Sample\AttributeEnablement\EnabledDataSource;
use Kassko\Sample\AttributeEnablement\DisabledDataSource;
use Kassko\Sample\AttributeEnablement\DisabledStoreEntity;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Integration tests for attribute enabled/disabled functionality.
 * 
 * Tests that attributes with enabled: false are properly ignored.
 */
class AttributeEnablementTest extends TestCase
{
    use LocalFixtureAutoloadTrait;

    private Hydrator $hydrator;
    private AttributeReader $reader;

    protected function setUp(): void
    {
        // Create service locator with data sources
        $serviceLocator = new ArrayServiceLocator([
            EnabledDataSource::class => new EnabledDataSource(),
            DisabledDataSource::class => new DisabledDataSource(),
        ]);
        
        // Create hydrator with service locator
        $builder = new DataMapperBuilder();
        $builder->addServiceLocator($serviceLocator);
        $dataMapper = $builder->build();
        $this->hydrator = $dataMapper->getHydrator();
        
        $this->reader = new AttributeReader();
    }

    // ==================== Disabled Property Tests ====================

    public function testDisabledPropertyIsNotHydrated(): void
    {
        $data = [
            'name' => 'Test Name',
            'description' => 'This should be ignored',
        ];

        $entity = $this->hydrator->hydrate(EnabledDisabledEntity::class, $data);

        // name should be hydrated
        $this->assertEquals('Test Name', $entity->getName());
        
        // disabledProperty has enabled: false, so it should NOT be hydrated
        $this->assertEquals('', $entity->getDisabledProperty());
    }

    // ==================== Disabled DataSource Tests ====================

    public function testDisabledDataSourceNotIncludedInStore(): void
    {
        $reflection = new ReflectionClass(EnabledDisabledEntity::class);
        $store = $this->reader->readDataSourcesStore($reflection);
        
        $this->assertNotNull($store);
        
        // Collect IDs from store items
        $sourceIds = array_map(fn($s) => $s->id, $store->items);
        
        // Enabled source should be present
        $this->assertContains('enabledSource', $sourceIds);
        
        // Disabled source should NOT be present
        $this->assertNotContains('disabledSource', $sourceIds);
    }

    public function testDisabledDataSourcesStoreReturnsNull(): void
    {
        $reflection = new ReflectionClass(DisabledStoreEntity::class);
        $store = $this->reader->readDataSourcesStore($reflection);
        
        // When the entire store is disabled, it should return null
        $this->assertNull($store);
    }

    // ==================== Cascading with Enabled/Disabled Tests ====================

    public function testCascadedStoreExcludesDisabledSources(): void
    {
        $reflection = new ReflectionClass(EnabledDisabledEntity::class);
        $store = $this->reader->readCascadedDataSourcesStore($reflection);
        
        $this->assertNotNull($store);
        
        // Collect IDs from cascaded store items
        $sourceIds = array_map(fn($s) => $s->id, $store->items);
        
        // Only enabled sources should be in the cascaded result
        $this->assertContains('enabledSource', $sourceIds);
        $this->assertNotContains('disabledSource', $sourceIds);
    }
}
