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
        new DataMapper(new \Kassko\DataMapper\ServiceResolver());
        $info = new Information();
        
        // Access the eager property - it should be loaded automatically
        // bestShop is marked with Loading::TYPE_EAGER, so it's loaded when we access any property
        $bestShop = $info->getBestShop();
        
        $this->assertNotNull($bestShop);
        $this->assertInstanceOf(\Kassko\Sample\Shop::class, $bestShop);
        $this->assertEquals('The best', $bestShop->getName());
    }
}
