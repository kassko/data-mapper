<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Tests\Integration;

use Kassko\DataMapper\DataMapper;
use Kassko\DataMapper\Registry\LoaderRegistry;
use Kassko\Sample\Information;
use PHPUnit\Framework\TestCase;

class EagerLoadingTest extends TestCase
{
    protected function tearDown(): void
    {
        LoaderRegistry::clear();
    }

    public function testEagerPropertiesAreLoadedImmediately(): void
    {
        new DataMapper();
        $info = new Information();
        
        // Call loadEagerProperties to trigger eager loading
        $info->loadEagerProperties();

        // Access the property without explicitly calling loadProperty
        // The property should already be loaded because it's marked as eager
        $bestShop = $info->getBestShop();
        
        $this->assertNotNull($bestShop);
        $this->assertInstanceOf(\Kassko\Sample\Shop::class, $bestShop);
        $this->assertEquals('The best', $bestShop->getName());
    }
}
