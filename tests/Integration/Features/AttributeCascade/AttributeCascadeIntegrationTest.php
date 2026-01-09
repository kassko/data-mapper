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

namespace Kassko\DataMapper\Tests\Integration;

use Kassko\DataMapper\DataCollector\AttributeCascadeCollector;
use Kassko\DataMapper\DataCollector\CascadeEvent;
use Kassko\DataMapper\Loader\Loader;
use Kassko\DataMapper\ServiceResolver;
use Kassko\DataMapper\ArrayServiceLocator;
use Kassko\DataMapper\Registry\LoaderRegistry;
use Kassko\DataMapper\Tests\TestHelpers\LocalFixtureAutoloadTrait;
use Kassko\Sample\Cascade\ChildEntity;
use Kassko\Sample\Cascade\ChildDataSource;
use Kassko\Sample\Cascade\ParentDataSource;
use Kassko\Sample\Cascade\TraitDataSource;
use Kassko\Sample\Cascade\ChildContainer;
use Kassko\Sample\Cascade\ChildProduct;
use Kassko\Sample\Cascade\ParentProduct;
use Kassko\Sample\Cascade\TraitProduct;
use Kassko\Sample\Cascade\ChildWithAttributes;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for PHP 8 attribute cascading.
 * 
 * Tests that DataSourcesStore and PropertyConfigStore are properly
 * cascaded from parent classes and traits.
 */
class AttributeCascadeIntegrationTest extends TestCase
{
    use LocalFixtureAutoloadTrait;

    private Loader $loader;
    private AttributeCascadeCollector $cascadeCollector;

    protected function setUp(): void
    {
        $this->cascadeCollector = new AttributeCascadeCollector();
        $this->cascadeCollector->enable();
        
        // Create service locator with data sources
        $locator = new ArrayServiceLocator([
            ChildDataSource::class => new ChildDataSource(),
            ParentDataSource::class => new ParentDataSource(),
            TraitDataSource::class => new TraitDataSource(),
        ]);
        
        $serviceResolver = new ServiceResolver($locator);
        
        $this->loader = new Loader(
            $serviceResolver,
            null, // logger
            [], // custom hydrators
            null, // lineage collector
            $this->cascadeCollector
        );
        
        // Register loader for LoadableTrait
        LoaderRegistry::set($this->loader);
    }

    protected function tearDown(): void
    {
        LoaderRegistry::clear();
    }

    // ==================== DataSourcesStore Cascading Integration Tests ====================

    public function testChildCanReferenceParentDataSource(): void
    {
        $entity = new ChildEntity();
        
        // Load property that references parent's DataSource
        $value = $entity->getFromParentSource();
        
        $this->assertEquals('from-parent', $value);
    }

    public function testChildCanReferenceTraitDataSource(): void
    {
        $entity = new ChildEntity();
        
        // Load property that references trait's DataSource
        $value = $entity->getFromTraitSource();
        
        $this->assertEquals('from-trait', $value);
    }

    public function testChildCanReferenceOwnDataSource(): void
    {
        $entity = new ChildEntity();
        
        // Load property that references child's own DataSource
        $value = $entity->getFromChildSource();
        
        $this->assertEquals('from-child', $value);
    }

    public function testChildOverridesParentDataSourceWithSameId(): void
    {
        $entity = new ChildEntity();
        
        // Load property that references sharedSource (defined in both parent and child)
        // Should use child's version
        $value = $entity->getFromSharedSource();
        
        $this->assertEquals('from-child-override', $value);
    }

    public function testCascadeCollectorRecordsDataSourceConflicts(): void
    {
        $entity = new ChildEntity();
        
        // Access a property to trigger DataSource map loading
        $entity->getFromChildSource();
        
        // Check that conflicts were recorded
        $conflicts = $this->cascadeCollector->getEventsByType(CascadeEvent::TYPE_DATASOURCE_ID_CONFLICT);
        $this->assertNotEmpty($conflicts, 'Should record DataSource ID conflicts');
    }

    // ==================== PropertyConfigStore Cascading Integration Tests ====================

    public function testPropertyCandidatesCanReferenceParentConfig(): void
    {
        $rawData = [
            'id' => 1,
            'item' => [
                'name' => 'Test Product',
                'parentType' => true, // Triggers parentConfig
            ],
        ];
        
        $reflection = new \ReflectionClass($this->loader);
        $method = $reflection->getMethod('hydrateObject');
        
        $container = new ChildContainer();
        $method->invoke($this->loader, $container, $rawData, null, 0);
        
        $item = $container->getItem();
        
        $this->assertInstanceOf(ParentProduct::class, $item);
        $this->assertEquals('parent', $item->getOrigin());
    }

    public function testPropertyCandidatesCanReferenceTraitConfig(): void
    {
        $rawData = [
            'id' => 1,
            'item' => [
                'name' => 'Test Product',
                'traitType' => true, // Triggers traitConfig
            ],
        ];
        
        $reflection = new \ReflectionClass($this->loader);
        $method = $reflection->getMethod('hydrateObject');
        
        $container = new ChildContainer();
        $method->invoke($this->loader, $container, $rawData, null, 0);
        
        $item = $container->getItem();
        
        $this->assertInstanceOf(TraitProduct::class, $item);
        $this->assertEquals('trait', $item->getOrigin());
    }

    public function testPropertyCandidatesCanReferenceChildConfig(): void
    {
        $rawData = [
            'id' => 1,
            'item' => [
                'name' => 'Test Product',
                'childType' => true, // Triggers childConfig
            ],
        ];
        
        $reflection = new \ReflectionClass($this->loader);
        $method = $reflection->getMethod('hydrateObject');
        
        $container = new ChildContainer();
        $method->invoke($this->loader, $container, $rawData, null, 0);
        
        $item = $container->getItem();
        
        $this->assertInstanceOf(ChildProduct::class, $item);
        $this->assertEquals('child', $item->getOrigin());
    }

    public function testDefaultConfigCandidateUsesChildOverride(): void
    {
        // No type indicator means defaultConfigCandidate is used (sharedConfig)
        // Child's sharedConfig (ChildProduct) should override parent's
        $rawData = [
            'id' => 1,
            'item' => [
                'name' => 'Test Product',
                // No type indicator
            ],
        ];
        
        $reflection = new \ReflectionClass($this->loader);
        $method = $reflection->getMethod('hydrateObject');
        
        $container = new ChildContainer();
        $method->invoke($this->loader, $container, $rawData, null, 0);
        
        $item = $container->getItem();
        
        $this->assertInstanceOf(ChildProduct::class, $item);
        $this->assertEquals('child', $item->getOrigin());
    }

    // ==================== Property Attribute Override Integration Tests ====================

    public function testChildPropertyAttributesTakePrecedence(): void
    {
        // Child has #[Property(sourceField: 'child_name')]
        // Parent has #[Property(sourceField: 'parent_name')]
        // Child's attribute should be used
        
        $rawData = [
            'id' => 1,
            'child_name' => 'John Doe', // Using child's mapping
            'parent_name' => 'Should be ignored', // Parent's mapping
        ];
        
        $reflection = new \ReflectionClass($this->loader);
        $method = $reflection->getMethod('hydrateObject');
        
        $entity = new ChildWithAttributes();
        $method->invoke($this->loader, $entity, $rawData, null, 0);
        
        $this->assertEquals('John Doe', $entity->getName());
    }

    public function testPropertyOverrideIsRecorded(): void
    {
        $rawData = [
            'id' => 1,
            'child_name' => 'John Doe',
        ];
        
        $reflection = new \ReflectionClass($this->loader);
        $method = $reflection->getMethod('hydrateObject');
        
        $entity = new ChildWithAttributes();
        $method->invoke($this->loader, $entity, $rawData, null, 0);
        
        // Check that override was recorded
        $overrides = $this->cascadeCollector->getEventsByType(CascadeEvent::TYPE_PROPERTY_ATTRIBUTE_OVERRIDE);
        $this->assertNotEmpty($overrides, 'Should record property attribute override');
    }

    // ==================== Cascade Summary Tests ====================

    public function testCascadeSummaryContainsAllEventTypes(): void
    {
        // Trigger various cascade events
        $entity = new ChildEntity();
        $entity->getFromChildSource();
        
        $container = new ChildContainer();
        $reflection = new \ReflectionClass($this->loader);
        $method = $reflection->getMethod('hydrateObject');
        $method->invoke($this->loader, $container, ['id' => 1, 'item' => ['name' => 'Test']], null, 0);
        
        $childWithAttrs = new ChildWithAttributes();
        $method->invoke($this->loader, $childWithAttrs, ['id' => 1, 'child_name' => 'Test'], null, 0);
        
        $summary = $this->cascadeCollector->getSummary();
        
        $this->assertGreaterThan(0, $summary['total']);
    }
}
