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
use Kassko\Sample\ElectricCarWithTrait;
use PHPUnit\Framework\TestCase;

class TraitHydrationTest extends TestCase
{
    public function testTraitPropertiesAreHydrated(): void
    {
        $lazyLoader = new Loader(new \Kassko\DataMapper\ServiceResolver());
        
        $data = [
            'id' => 1,
            'brand' => 'Tesla',
            'model' => 'Model S',
            'energyProvider' => 'Tesla Supercharger'
        ];
        
        $electricCar = new ElectricCarWithTrait();
        
        // Use reflection to call the private hydrateObject method
        $reflection = new \ReflectionClass($lazyLoader);
        $method = $reflection->getMethod('hydrateObject');
        $method->invoke($lazyLoader, $electricCar, $data, null, 0);
        
        // All properties should be hydrated, including trait properties
        $this->assertEquals(1, $electricCar->getId());
        $this->assertEquals('Tesla', $electricCar->getBrand());
        $this->assertEquals('Model S', $electricCar->getModel()); // From trait
        $this->assertEquals('Tesla Supercharger', $electricCar->getEnergyProvider());
    }
}
