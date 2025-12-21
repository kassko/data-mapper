<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Tests\Integration;

use Kassko\DataMapper\DataMapper;
use Kassko\DataMapper\Registry\LazyLoaderRegistry;
use Kassko\Sample\Garage;
use Kassko\Sample\GasolineCar;
use Kassko\Sample\ElectricCar;
use PHPUnit\Framework\TestCase;

class PropertyCandidatesTest extends TestCase
{
    protected function tearDown(): void
    {
        LazyLoaderRegistry::clear();
    }

    public function testPropertyCandidatesWithGasolineCar(): void
    {
        new DataMapper();
        
        $garage = new Garage(2);
        $cars = $garage->getCars();
        
        $this->assertCount(1, $cars);
        $this->assertInstanceOf(GasolineCar::class, $cars[0]);
        $this->assertEquals('Chevrolet', $cars[0]->getBrand());
        $this->assertEquals('regular', $cars[0]->getGasolineKind());
    }

    public function testPropertyCandidatesWithMixedCars(): void
    {
        new DataMapper();
        
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
