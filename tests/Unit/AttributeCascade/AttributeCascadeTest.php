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

namespace Kassko\DataMapper\Tests\Unit;

use Kassko\DataMapper\DataCollector\AttributeCascadeCollector;
use Kassko\DataMapper\DataCollector\CascadeEvent;
use Kassko\DataMapper\Metadata\AttributeReader;
use Kassko\DataMapper\Tests\TestHelpers\LocalFixtureAutoloadTrait;
use Kassko\Sample\Cascade\BaseEntity;
use Kassko\Sample\Cascade\ChildEntity;
use Kassko\Sample\Cascade\BaseContainer;
use Kassko\Sample\Cascade\ChildContainer;
use Kassko\Sample\Cascade\ParentWithAttributes;
use Kassko\Sample\Cascade\ChildWithAttributes;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Unit tests for AttributeReader cascading functionality.
 */
class AttributeCascadeTest extends TestCase
{
    use LocalFixtureAutoloadTrait;

    private AttributeReader $reader;
    private AttributeCascadeCollector $collector;

    protected function setUp(): void
    {
        $this->collector = new AttributeCascadeCollector();
        $this->collector->enable();
        $this->reader = new AttributeReader($this->collector);
    }

    // ==================== DataSourcesStore Cascading Tests ====================

    public function testCascadedDataSourcesStoreIncludesParentSources(): void
    {
        $reflection = new ReflectionClass(ChildEntity::class);
        $store = $this->reader->readCascadedDataSourcesStore($reflection);
        
        $this->assertNotNull($store);
        
        // Should have sources from child, parent, and trait
        $sourceIds = array_map(fn($s) => $s->id, $store->items);
        
        $this->assertContains('childSource', $sourceIds, 'Should include child source');
        $this->assertContains('parentSource', $sourceIds, 'Should include parent source');
        $this->assertContains('traitSource', $sourceIds, 'Should include trait source');
        $this->assertContains('sharedSource', $sourceIds, 'Should include shared source');
    }

    public function testCascadedDataSourcesStoreChildOverridesParent(): void
    {
        $reflection = new ReflectionClass(ChildEntity::class);
        $store = $this->reader->readCascadedDataSourcesStore($reflection);
        
        $this->assertNotNull($store);
        
        // Find the sharedSource - should be the child's version
        $sharedSource = null;
        foreach ($store->items as $source) {
            if ($source->id === 'sharedSource') {
                $sharedSource = $source;
                break;
            }
        }
        
        $this->assertNotNull($sharedSource);
        // Child's ChildDataSource should override parent's ParentDataSource
        $this->assertStringContainsString('ChildDataSource', $sharedSource->class);
    }

    public function testCascadedDataSourcesStoreRecordsConflicts(): void
    {
        $reflection = new ReflectionClass(ChildEntity::class);
        $this->reader->readCascadedDataSourcesStore($reflection);
        
        // Should have recorded ID conflict for sharedSource
        $conflicts = $this->collector->getEventsByType(CascadeEvent::TYPE_DATASOURCE_ID_CONFLICT);
        $this->assertNotEmpty($conflicts, 'Should record DataSource ID conflict');
        
        $conflictIds = array_map(fn($e) => $e->metadata['dataSourceId'] ?? '', $conflicts);
        $this->assertContains('sharedSource', $conflictIds);
    }

    public function testCascadedDataSourcesStoreRecordsMerges(): void
    {
        $reflection = new ReflectionClass(ChildEntity::class);
        $this->reader->readCascadedDataSourcesStore($reflection);
        
        // Should have recorded merges from parent and trait
        $merges = $this->collector->getEventsByType(CascadeEvent::TYPE_DATASOURCES_STORE_MERGE);
        $this->assertNotEmpty($merges, 'Should record DataSourcesStore merges');
    }

    // ==================== PropertyConfigStore Cascading Tests ====================

    public function testCascadedPropertyConfigStoreIncludesParentConfigs(): void
    {
        $reflection = new ReflectionClass(ChildContainer::class);
        $store = $this->reader->readCascadedPropertyConfigStore($reflection);
        
        $this->assertNotNull($store);
        
        // Should have configs from child, parent, and trait
        $configIds = array_keys($store->items);
        
        $this->assertContains('childConfig', $configIds, 'Should include child config');
        $this->assertContains('parentConfig', $configIds, 'Should include parent config');
        $this->assertContains('traitConfig', $configIds, 'Should include trait config');
        $this->assertContains('sharedConfig', $configIds, 'Should include shared config');
    }

    public function testCascadedPropertyConfigStoreChildOverridesParent(): void
    {
        $reflection = new ReflectionClass(ChildContainer::class);
        $store = $this->reader->readCascadedPropertyConfigStore($reflection);
        
        $this->assertNotNull($store);
        
        // The sharedConfig should be the child's version
        $sharedConfig = $store->items['sharedConfig'] ?? null;
        
        $this->assertNotNull($sharedConfig);
        // Child's ChildProduct should override parent's ParentProduct
        $this->assertStringContainsString('ChildProduct', $sharedConfig->class);
    }

    public function testCascadedPropertyConfigStoreRecordsConflicts(): void
    {
        $reflection = new ReflectionClass(ChildContainer::class);
        $this->reader->readCascadedPropertyConfigStore($reflection);
        
        // Should have recorded ID conflict for sharedConfig
        $conflicts = $this->collector->getEventsByType(CascadeEvent::TYPE_PROPERTY_CONFIG_ID_CONFLICT);
        $this->assertNotEmpty($conflicts, 'Should record PropertyConfig ID conflict');
        
        $conflictIds = array_map(fn($e) => $e->metadata['configId'] ?? '', $conflicts);
        $this->assertContains('sharedConfig', $conflictIds);
    }

    public function testCascadedPropertyConfigStoreRecordsMerges(): void
    {
        $reflection = new ReflectionClass(ChildContainer::class);
        $this->reader->readCascadedPropertyConfigStore($reflection);
        
        // Should have recorded merges from parent and trait
        $merges = $this->collector->getEventsByType(CascadeEvent::TYPE_PROPERTY_CONFIG_STORE_MERGE);
        $this->assertNotEmpty($merges, 'Should record PropertyConfigStore merges');
    }

    // ==================== Property Attribute Override Tests ====================

    public function testGetAllPropertiesRecordsAttributeOverride(): void
    {
        $reflection = new ReflectionClass(ChildWithAttributes::class);
        $properties = $this->reader->getAllProperties($reflection);
        
        // Should have recorded property attribute override
        $overrides = $this->collector->getEventsByType(CascadeEvent::TYPE_PROPERTY_ATTRIBUTE_OVERRIDE);
        $this->assertNotEmpty($overrides, 'Should record property attribute override');
        
        $propertyNames = array_map(fn($e) => $e->metadata['propertyName'] ?? '', $overrides);
        $this->assertContains('name', $propertyNames);
    }

    public function testGetAllPropertiesUsesChildProperty(): void
    {
        $reflection = new ReflectionClass(ChildWithAttributes::class);
        $properties = $this->reader->getAllProperties($reflection);
        
        // Should have the name property from child class
        $this->assertArrayHasKey('name', $properties);
        
        // The property should be from ChildWithAttributes
        $this->assertEquals(ChildWithAttributes::class, $properties['name']->getDeclaringClass()->getName());
    }

    // ==================== getDataSourceMap Integration ====================

    public function testGetDataSourceMapUsesCascading(): void
    {
        $entity = new ChildEntity();
        $map = $this->reader->getDataSourceMap($entity);
        
        // Should have sources from child, parent, and trait
        $this->assertArrayHasKey('childSource', $map);
        $this->assertArrayHasKey('parentSource', $map);
        $this->assertArrayHasKey('traitSource', $map);
        $this->assertArrayHasKey('sharedSource', $map);
        
        // sharedSource should be child's version
        $this->assertStringContainsString('ChildDataSource', $map['sharedSource']->class);
    }

    // ==================== Collector Summary ====================

    public function testCollectorSummary(): void
    {
        $reflection = new ReflectionClass(ChildEntity::class);
        $this->reader->readCascadedDataSourcesStore($reflection);
        
        $summary = $this->collector->getSummary();
        
        $this->assertArrayHasKey('dataSourcesStoreMerges', $summary);
        $this->assertArrayHasKey('propertyConfigStoreMerges', $summary);
        $this->assertArrayHasKey('dataSourceIdConflicts', $summary);
        $this->assertArrayHasKey('propertyConfigIdConflicts', $summary);
        $this->assertArrayHasKey('propertyAttributeOverrides', $summary);
        $this->assertArrayHasKey('total', $summary);
        
        $this->assertGreaterThan(0, $summary['total']);
    }
}
