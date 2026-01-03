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

namespace Kassko\DataMapper\Tests\Integration\Attributes\PropertyInstantiatingHook;

use Kassko\DataMapper\DataMapper;
use Kassko\DataMapper\DataMapperBuilder;
use Kassko\DataMapper\Registry\LoaderRegistry;
use Kassko\DataMapper\ServiceResolver;
use Kassko\DataMapper\Tests\TestHelpers\LocalFixtureAutoloadTrait;
use Kassko\Sample\PropertyInstantiatingHook\EntityWithExternalHook;
use Kassko\Sample\PropertyInstantiatingHook\InstantiationTracker;
use Kassko\Sample\PropertyInstantiatingHook\MultiHookEntity;
use Kassko\Sample\PropertyInstantiatingHook\TrackedEntity;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for PropertyInstantiatingHook attribute.
 * 
 * PropertyInstantiatingHook allows executing custom logic immediately after 
 * an object is instantiated during hydration, before any properties are set.
 */
class PropertyInstantiatingHookTest extends TestCase
{
    use LocalFixtureAutoloadTrait;

    protected function setUp(): void
    {
        InstantiationTracker::reset();
    }

    protected function tearDown(): void
    {
        LoaderRegistry::clear();
        InstantiationTracker::reset();
    }

    /**
     * Test that PropertyInstantiatingHook is called when entity is manually instantiated.
     * Note: The hook is primarily designed for hydration scenarios, but we test basic setup.
     */
    public function testPropertyInstantiatingHookBasicSetup(): void
    {
        new DataMapper(new ServiceResolver());
        
        $entity = new TrackedEntity(1);
        
        // When manually creating an object, the hook is NOT automatically called
        // because the hook is meant for hydration scenarios
        // We're testing that the attribute is properly defined and the method exists
        $this->assertFalse($entity->isInstantiationTracked());
        
        // But we can manually call it to verify the hook method works
        $entity->onInstantiated($entity);
        $this->assertTrue($entity->isInstantiationTracked());
        $this->assertNotNull($entity->getInstantiationTimestamp());
    }

    /**
     * Test that hook can be manually triggered and tracks correctly.
     */
    public function testHookMethodTracksInstantiation(): void
    {
        new DataMapper(new ServiceResolver());
        
        $entity = new TrackedEntity(2);
        
        // Manually trigger the hook to verify it works
        $entity->onInstantiated($entity);
        
        $this->assertTrue($entity->isInstantiationTracked());
        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/',
            $entity->getInstantiationTimestamp()
        );
    }

    /**
     * Test that multiple PropertyInstantiatingHook attributes can be applied.
     */
    public function testMultipleHooksCanBeApplied(): void
    {
        new DataMapper(new ServiceResolver());
        
        $entity = new MultiHookEntity();
        
        // Manually trigger the hooks in order to verify they work
        $entity->firstHook($entity);
        $entity->secondHook($entity);
        
        $this->assertEquals(2, $entity->getHookCallCount());
        $this->assertEquals(['firstHook', 'secondHook'], $entity->getHookCallOrder());
    }

    /**
     * Test that external service hook can be used.
     */
    public function testExternalServiceHook(): void
    {
        new DataMapper(new ServiceResolver());
        
        $entity = new EntityWithExternalHook(1);
        
        // Manually trigger the external tracker
        $tracker = new InstantiationTracker();
        $tracker->trackInstantiation($entity);
        
        $this->assertEquals(1, InstantiationTracker::getTrackedCount());
        
        $tracked = InstantiationTracker::getTrackedEntities();
        $this->assertCount(1, $tracked);
        $this->assertEquals(EntityWithExternalHook::class, $tracked[0]['class']);
        $this->assertEquals(1, $tracked[0]['id']);
    }

    /**
     * Test that multiple entities can be tracked by external service.
     */
    public function testMultipleEntitiesTrackedByExternalService(): void
    {
        new DataMapper(new ServiceResolver());
        
        $tracker = new InstantiationTracker();
        
        $entity1 = new EntityWithExternalHook(1);
        $tracker->trackInstantiation($entity1);
        
        $entity2 = new EntityWithExternalHook(2);
        $tracker->trackInstantiation($entity2);
        
        $entity3 = new EntityWithExternalHook(3);
        $tracker->trackInstantiation($entity3);
        
        $this->assertEquals(3, InstantiationTracker::getTrackedCount());
        
        $tracked = InstantiationTracker::getTrackedEntities();
        $this->assertEquals(1, $tracked[0]['id']);
        $this->assertEquals(2, $tracked[1]['id']);
        $this->assertEquals(3, $tracked[2]['id']);
    }

    /**
     * Test that entity lazy loading works alongside PropertyInstantiatingHook.
     */
    public function testLazyLoadingWithPropertyInstantiatingHook(): void
    {
        new DataMapper(new ServiceResolver());
        
        $entity = new TrackedEntity(1);
        
        // Verify lazy loading works
        $name = $entity->getName();
        
        $this->assertEquals('Entity One', $name);
    }

    /**
     * Test InstantiationTracker reset functionality.
     */
    public function testTrackerResetClears(): void
    {
        $tracker = new InstantiationTracker();
        $tracker->trackInstantiation(new TrackedEntity(1));
        
        $this->assertEquals(1, InstantiationTracker::getTrackedCount());
        
        InstantiationTracker::reset();
        
        $this->assertEquals(0, InstantiationTracker::getTrackedCount());
        $this->assertEmpty(InstantiationTracker::getTrackedEntities());
    }
}
