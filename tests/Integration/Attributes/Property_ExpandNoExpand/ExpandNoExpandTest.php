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

use Kassko\DataMapper\Loader\Loader;
use Kassko\DataMapper\Attribute\Property;
use Kassko\Sample\PropertyExpandNoExpand\Shop;
use PHPUnit\Framework\TestCase;

class ExpandNoExpandTest extends TestCase
{
    public function testExpandOnlySpecifiedProperties(): void
    {
        $lazyLoader = new Loader(new \Kassko\DataMapper\ServiceResolver());
        
        $data = [
            'name' => 'The best shop',
            'address' => 'Main Street',
            'phone' => '555-1234'
        ];
        
        $shop = new Shop();
        
        // Create a Property attribute with expand set to only 'name'
        $propertyAttr = new Property(expand: 'name');
        
        // Use reflection to call the private hydrateObject method
        $reflection = new \ReflectionClass($lazyLoader);
        $method = $reflection->getMethod('hydrateObject');
        $method->invoke($lazyLoader, $shop, $data, $propertyAttr, 0);
        
        // Only 'name' should be hydrated
        $this->assertEquals('The best shop', $shop->getName());
        
        // 'address' should not be hydrated (not in expand list)
        $this->assertNull($shop->getAddress());
    }

    public function testNoExpandExcludesSpecifiedProperties(): void
    {
        $lazyLoader = new Loader(new \Kassko\DataMapper\ServiceResolver());
        
        $data = [
            'name' => 'The worst shop',
            'address' => 'Back Street'
        ];
        
        $shop = new Shop();
        
        // Create a Property attribute with noExpand set to 'name'
        $propertyAttr = new Property(noExpand: 'name');
        
        // Use reflection to call the private hydrateObject method
        $reflection = new \ReflectionClass($lazyLoader);
        $method = $reflection->getMethod('hydrateObject');
        $method->invoke($lazyLoader, $shop, $data, $propertyAttr, 0);
        
        // 'name' should not be hydrated (in noExpand list)
        $this->assertNull($shop->getName());
        
        // 'address' should be hydrated
        $this->assertEquals('Back Street', $shop->getAddress());
    }
}
