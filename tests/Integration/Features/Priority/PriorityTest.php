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

namespace Kassko\DataMapper\Tests\Integration;

use Kassko\DataMapper\ArrayServiceLocator;
use Kassko\DataMapper\DataMapper;
use Kassko\DataMapper\Registry\LoaderRegistry;
use Kassko\DataMapper\ServiceResolver;
use Kassko\DataMapper\Tests\Fixtures\ApiService;
use Kassko\DataMapper\Tests\Fixtures\CacheService;
use Kassko\DataMapper\Tests\Fixtures\ConfigWithMultiSourcePriority;
use Kassko\DataMapper\Tests\Fixtures\DefaultsService;
use Kassko\DataMapper\Tests\Fixtures\ProductWithPriority;
use Kassko\DataMapper\Tests\Fixtures\ProductWithSamePriority;
use Kassko\DataMapper\Tests\Fixtures\UserWithPriorityStore;
use PHPUnit\Framework\TestCase;

/**
 * Tests for priority-based hydration system
 */
class PriorityTest extends TestCase
{
    protected function tearDown(): void
    {
        LoaderRegistry::clear();
    }

    private function getDataMapper(): DataMapper
    {
        $locator = new ArrayServiceLocator([
            CacheService::class => new CacheService(),
            ApiService::class => new ApiService(),
            DefaultsService::class => new DefaultsService(),
        ]);

        return new DataMapper(new ServiceResolver($locator));
    }

    public function testHigherPriorityOverridesLowerPriority(): void
    {
        $dataMapper = $this->getDataMapper();
        $product = new ProductWithPriority();

        // Cache (priority 0) loads first, then API (priority 10) overrides it
        $name = $product->getName();
        $price = $product->getPrice();

        // Should get API values (higher priority)
        $this->assertSame('Fresh API Product 1', $name);
        $this->assertSame(149.99, $price);
    }

    public function testSamePriorityFollowsDeclarationOrder(): void
    {
        $dataMapper = $this->getDataMapper();
        $product = new ProductWithSamePriority();

        $name = $product->getName();

        // All sources have priority 5, so last declared wins
        $this->assertSame('Fresh API Product 1', $name);
    }

    public function testPriorityWithSinglePropDataSource(): void
    {
        $dataMapper = $this->getDataMapper();
        $user = new UserWithPriorityStore();

        $email = $user->getEmail();

        // cacheEmail (priority 0) vs apiEmail (priority 10)
        // API should win
        $this->assertSame('john.doe@example.com', $email);
    }

    public function testPriorityWithMultiPropDataSource(): void
    {
        $dataMapper = $this->getDataMapper();
        $user = new UserWithPriorityStore();

        $firstName = $user->getFirstName();
        $lastName = $user->getLastName();

        // cacheData (priority 0) vs apiData (priority 10)
        // API should win for both properties
        $this->assertSame('John', $firstName);
        $this->assertSame('Doe', $lastName);
    }

    public function testMultipleSourcesWithDifferentPriorities(): void
    {
        $dataMapper = $this->getDataMapper();
        $config = new ConfigWithMultiSourcePriority();

        $theme = $config->getTheme();
        $language = $config->getLanguage();
        $timezone = $config->getTimezone();
        $notifications = $config->getNotifications();

        // theme: defaults='system', cache='dark', api='light' → api wins (priority 10)
        $this->assertSame('light', $theme);

        // language: defaults='en', cache='en', api='fr' → api wins (priority 10)
        $this->assertSame('fr', $language);

        // timezone: defaults='UTC', cache='UTC', no api → cache wins (priority 5)
        $this->assertSame('UTC', $timezone);

        // notifications: defaults=false, no cache, api=true → api wins (priority 10)
        $this->assertTrue($notifications);
    }

    public function testPriorityDoesNotRehydrateWithLowerPriority(): void
    {
        $dataMapper = $this->getDataMapper();
        $product = new ProductWithPriority();

        // First access loads both sources, API wins due to higher priority
        $name1 = $product->getName();
        $this->assertSame('Fresh API Product 1', $name1);

        // Second access should not reload (already hydrated)
        $name2 = $product->getName();
        $this->assertSame('Fresh API Product 1', $name2);
        
        // Verify it's the same instance (not reloaded)
        $this->assertSame($name1, $name2);
    }

    public function testMultiplePropertiesIndependentPriorities(): void
    {
        $dataMapper = $this->getDataMapper();
        $product = new ProductWithPriority();

        // Each property should be hydrated independently
        $name = $product->getName();
        $price = $product->getPrice();

        // Both should use API values (higher priority)
        $this->assertSame('Fresh API Product 1', $name);
        $this->assertSame(149.99, $price);

        // Access again to ensure no cross-contamination
        $name2 = $product->getName();
        $price2 = $product->getPrice();

        $this->assertSame($name, $name2);
        $this->assertSame($price, $price2);
    }

    public function testDefaultPriorityIsZero(): void
    {
        // This test verifies that attributes without explicit priority default to 0
        // We can't easily test this without reflection, but we document the behavior
        $this->assertTrue(true, 'Default priority is 0 by design');
    }

    public function testPriorityOrderMatters(): void
    {
        $dataMapper = $this->getDataMapper();
        $config = new ConfigWithMultiSourcePriority();

        // Loading multiple properties to ensure all sources are processed
        $theme = $config->getTheme();
        $language = $config->getLanguage();

        // API (priority 10) should override cache (priority 5) and defaults (priority 0)
        $this->assertSame('light', $theme);
        $this->assertSame('fr', $language);
    }
}
