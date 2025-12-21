<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Tests\Integration;

use Kassko\DataMapper\DataMapper;
use Kassko\DataMapper\Registry\LazyLoaderRegistry;
use Kassko\Sample\Information;
use PHPUnit\Framework\TestCase;

class RecursiveHydrationTest extends TestCase
{
    protected function tearDown(): void
    {
        LazyLoaderRegistry::clear();
    }

    public function testRecursiveHydrationWithClassAttribute(): void
    {
        new DataMapper();
        $info = new Information();

        // Load the bestShop property (marked as eager but we're testing the recursive hydration)
        $bestShop = $info->getBestShop();
        
        $this->assertNotNull($bestShop);
        $this->assertInstanceOf(\Kassko\Sample\Shop::class, $bestShop);
        $this->assertEquals('The best', $bestShop->getName());
        $this->assertEquals('Street of the best', $bestShop->getAddress());
    }

    public function testRecursiveHydrationWithMultipleProperties(): void
    {
        new DataMapper();
        $info = new Information();

        $bestShop = $info->getBestShop();
        $worstShop = $info->getWorstShop();
        
        $this->assertNotNull($bestShop);
        $this->assertInstanceOf(\Kassko\Sample\Shop::class, $bestShop);
        $this->assertEquals('The best', $bestShop->getName());
        
        $this->assertNotNull($worstShop);
        $this->assertInstanceOf(\Kassko\Sample\Shop::class, $worstShop);
        $this->assertEquals('The worst', $worstShop->getName());
        $this->assertEquals('Street of the worst', $worstShop->getAddress());
    }
}
