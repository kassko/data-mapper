<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Tests\Integration;

use Kassko\DataMapper\LazyLoader\LazyLoader;
use Kassko\Sample\ProductWithSkip;
use Kassko\Sample\ProductWithSkipAll;
use PHPUnit\Framework\TestCase;

class PropertyInclusionTest extends TestCase
{
    public function testSkipPropertyIsNotHydrated(): void
    {
        $lazyLoader = new LazyLoader();
        
        $data = [
            'name' => 'Widget',
            'internalNote' => 'Secret note',
            'price' => 19.99
        ];
        
        $product = new ProductWithSkip();
        
        // Use reflection to call the private hydrateObject method
        $reflection = new \ReflectionClass($lazyLoader);
        $method = $reflection->getMethod('hydrateObject');
        $method->invoke($lazyLoader, $product, $data, null, 0);
        
        // name and price should be hydrated
        $this->assertEquals('Widget', $product->getName());
        $this->assertEquals(19.99, $product->getPrice());
        
        // internalNote should NOT be hydrated (marked with SkipProperty)
        $this->assertNull($product->getInternalNote());
    }

    public function testSkipAllPropertiesOnlyHydratesMarkedProperties(): void
    {
        $lazyLoader = new LazyLoader();
        
        $data = [
            'name' => 'Gadget',
            'description' => 'A wonderful gadget',
            'price' => 29.99
        ];
        
        $product = new ProductWithSkipAll();
        
        // Use reflection to call the private hydrateObject method
        $reflection = new \ReflectionClass($lazyLoader);
        $method = $reflection->getMethod('hydrateObject');
        $method->invoke($lazyLoader, $product, $data, null, 0);
        
        // Only description should be hydrated (marked with Property)
        $this->assertEquals('A wonderful gadget', $product->getDescription());
        
        // name and price should NOT be hydrated (class has SkipAllProperties)
        $this->assertNull($product->getName());
        $this->assertNull($product->getPrice());
    }
}
