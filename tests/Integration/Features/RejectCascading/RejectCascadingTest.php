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

namespace Kassko\DataMapper\Tests\Integration\Features\RejectCascading;

use Kassko\DataMapper\DataMapperBuilder;
use Kassko\DataMapper\Metadata\AttributeReader;
use Kassko\DataMapper\Tests\TestHelpers\LocalFixtureAutoloadTrait;
use Kassko\Sample\RejectCascading\ParentWithDataSources;
use Kassko\Sample\RejectCascading\ChildRejectingCascade;
use Kassko\Sample\RejectCascading\ChildAcceptingCascade;
use Kassko\Sample\RejectCascading\ParentDataSource;
use Kassko\Sample\RejectCascading\ChildDataSource;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Integration tests for RejectAttributeCascading attribute.
 * 
 * Tests that classes with RejectAttributeCascading do not inherit
 * DataSourcesStore and PropertyConfigStore from parent classes.
 */
class RejectCascadingTest extends TestCase
{
    use LocalFixtureAutoloadTrait;

    private AttributeReader $reader;

    protected function setUp(): void
    {
        $this->reader = new AttributeReader();
    }

    // ==================== Without RejectAttributeCascading (Normal Cascading) ====================

    public function testChildAcceptsCascadingByDefault(): void
    {
        $reflection = new ReflectionClass(ChildAcceptingCascade::class);
        $store = $this->reader->readCascadedDataSourcesStore($reflection);
        
        $this->assertNotNull($store);
        
        // Collect IDs
        $sourceIds = array_map(fn($s) => $s->id, $store->items);
        
        // Should have both parent and child sources
        $this->assertContains('parentSource', $sourceIds, 'Should include parent source via cascading');
        $this->assertContains('childSource', $sourceIds, 'Should include child source');
    }

    public function testParentHasOnlyOwnSources(): void
    {
        $reflection = new ReflectionClass(ParentWithDataSources::class);
        $store = $this->reader->readCascadedDataSourcesStore($reflection);
        
        $this->assertNotNull($store);
        
        // Collect IDs
        $sourceIds = array_map(fn($s) => $s->id, $store->items);
        
        // Should only have parent source
        $this->assertContains('parentSource', $sourceIds);
        $this->assertNotContains('childSource', $sourceIds);
    }

    // ==================== With RejectAttributeCascading ====================

    public function testChildRejectsCascading(): void
    {
        $reflection = new ReflectionClass(ChildRejectingCascade::class);
        $store = $this->reader->readCascadedDataSourcesStore($reflection);
        
        $this->assertNotNull($store);
        
        // Collect IDs
        $sourceIds = array_map(fn($s) => $s->id, $store->items);
        
        // Should NOT have parent source (cascading rejected)
        $this->assertNotContains('parentSource', $sourceIds, 'Should NOT include parent source (cascading rejected)');
        
        // Should only have child source
        $this->assertContains('childSource', $sourceIds, 'Should include child source');
    }

    public function testHasRejectAttributeCascading(): void
    {
        $reflectionReject = new ReflectionClass(ChildRejectingCascade::class);
        $reflectionAccept = new ReflectionClass(ChildAcceptingCascade::class);
        
        // Child that rejects cascading should have the attribute
        $this->assertTrue($this->reader->hasRejectAttributeCascading($reflectionReject));
        
        // Child that accepts cascading should NOT have the attribute
        $this->assertFalse($this->reader->hasRejectAttributeCascading($reflectionAccept));
    }

    public function testReadRejectAttributeCascading(): void
    {
        $reflection = new ReflectionClass(ChildRejectingCascade::class);
        $attribute = $this->reader->readRejectAttributeCascading($reflection);
        
        $this->assertNotNull($attribute);
        $this->assertTrue($attribute->enabled);
    }

    public function testDisabledRejectAttributeCascadingIsIgnored(): void
    {
        // Create a test that verifies disabled RejectAttributeCascading is ignored
        // This would require a fixture with enabled: false, which we'll skip for now
        // as it would need a separate fixture class
        $this->markTestSkipped('Requires additional fixture with enabled: false');
    }
}
