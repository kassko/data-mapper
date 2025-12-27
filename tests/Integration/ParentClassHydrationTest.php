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
use Kassko\Sample\ElectricCar;
use PHPUnit\Framework\TestCase;

class ParentClassHydrationTest extends TestCase
{
    public function testParentClassPropertiesAreHydrated(): void
    {
        $lazyLoader = new Loader(new \Kassko\DataMapper\ServiceResolver());
        
        $data = [
            'id' => 1,
            'brand' => 'Tesla',
            'energyProvider' => 'Tesla Supercharger'
        ];
        
        $electricCar = new ElectricCar();
        
        // Use reflection to call the private hydrateObject method
        $reflection = new \ReflectionClass($lazyLoader);
        $method = $reflection->getMethod('hydrateObject');
        $method->invoke($lazyLoader, $electricCar, $data, null, 0);
        
        // All properties should be hydrated, including parent class properties
        $this->assertEquals(1, $electricCar->getId());
        $this->assertEquals('Tesla', $electricCar->getBrand());
        $this->assertEquals('Tesla Supercharger', $electricCar->getEnergyProvider());
    }
}
