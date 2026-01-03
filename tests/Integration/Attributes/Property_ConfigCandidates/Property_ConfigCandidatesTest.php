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

use Kassko\DataMapper\DataMapper;
use Kassko\DataMapper\Registry\LoaderRegistry;
use Kassko\DataMapper\Tests\TestHelpers\LocalFixtureAutoloadTrait;
use Kassko\Sample\PropertyConfigCandidates\Garage;
use Kassko\Sample\PropertyConfigCandidates\GasolineCar;
use Kassko\Sample\PropertyConfigCandidates\ElectricCar;
use PHPUnit\Framework\TestCase;

class Property_ConfigCandidatesTest extends TestCase
{
    use LocalFixtureAutoloadTrait;

    protected function tearDown(): void
    {
        LoaderRegistry::clear();
    }

    public function testPropertyCandidatesWithGasolineCar(): void
    {
        new DataMapper(new \Kassko\DataMapper\ServiceResolver());
        
        $garage = new Garage(2);
        $cars = $garage->getCars();
        
        $this->assertCount(1, $cars);
        $this->assertInstanceOf(GasolineCar::class, $cars[0]);
        $this->assertEquals('Chevrolet', $cars[0]->getBrand());
        $this->assertEquals('regular', $cars[0]->getGasolineKind());
    }

    public function testPropertyCandidatesWithMixedCars(): void
    {
        new DataMapper(new \Kassko\DataMapper\ServiceResolver());
        
        $garage = new Garage(1);
        $cars = $garage->getCars();
        
        $this->assertCount(2, $cars);
        
        // First car should be GasolineCar (has gasoline_kind)
        $this->assertInstanceOf(GasolineCar::class, $cars[0]);
        $this->assertEquals('Ford', $cars[0]->getBrand());
        $this->assertEquals('premium', $cars[0]->getGasolineKind());
        
        // Second car should be ElectricCar (has energy_provider)
        $this->assertInstanceOf(ElectricCar::class, $cars[1]);
        $this->assertEquals('Tesla', $cars[1]->getBrand());
        $this->assertEquals('supercharger', $cars[1]->getEnergyProvider());
    }
}
