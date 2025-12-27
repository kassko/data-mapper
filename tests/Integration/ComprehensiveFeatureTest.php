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
use Kassko\Sample\Garage;
use Kassko\Sample\GasolineCar;
use Kassko\Sample\ElectricCar;
use PHPUnit\Framework\TestCase;

/**
 * Comprehensive test showcasing all three major features working together:
 * 1. Expression language evolution (##object, object(), rawDataItem(), rawDataItemExists())
 * 2. Hook attribute for lifecycle callbacks
 * 3. PropertyCandidates for polymorphic/runtime property resolution
 */
class ComprehensiveFeatureTest extends TestCase
{
    protected function tearDown(): void
    {
        LoaderRegistry::clear();
    }

    public function testAllFeaturesWorkTogether(): void
    {
        new DataMapper(new \Kassko\DataMapper\ServiceResolver());
        
        // Create a garage with mixed car types
        $garage = new Garage(1);
        
        // Load cars - this will:
        // 1. Execute DataSource with #id argument (expression reference)
        // 2. Use PropertyCandidates with rawDataItemExists() to determine car type
        // 3. Call after_create_object hook on each car as it's hydrated
        // 4. Call after_set_property hook when cars array is set with ##object and #cars
        $cars = $garage->getCars();
        
        // Verify all features worked correctly
        $this->assertCount(2, $cars, 'Should have loaded 2 cars');
        
        // First car should be GasolineCar (discriminator matched gasolineKind field)
        $this->assertInstanceOf(GasolineCar::class, $cars[0]);
        $this->assertEquals('Ford', $cars[0]->getBrand());
        $this->assertEquals('Mustang', $cars[0]->getModel());
        $this->assertEquals('premium', $cars[0]->getGasolineKind());
        
        // Second car should be ElectricCar (discriminator matched energyProvider field)
        $this->assertInstanceOf(ElectricCar::class, $cars[1]);
        $this->assertEquals('Tesla', $cars[1]->getBrand());
        $this->assertEquals('Model 3', $cars[1]->getModel());
        $this->assertEquals('supercharger', $cars[1]->getEnergyProvider());
        
        // Verify hooks were executed
        $this->assertTrue($garage->isCarsLoaded(), 'after_set_property hook should have been called');
    }

    public function testPropertyCandidatesHandlesDifferentDataShapes(): void
    {
        new DataMapper(new \Kassko\DataMapper\ServiceResolver());
        
        // Garage 1 has mixed cars
        $garage1 = new Garage(1);
        $cars1 = $garage1->getCars();
        $this->assertCount(2, $cars1);
        $this->assertInstanceOf(GasolineCar::class, $cars1[0]);
        $this->assertInstanceOf(ElectricCar::class, $cars1[1]);
        
        // Garage 2 has only gasoline cars
        $garage2 = new Garage(2);
        $cars2 = $garage2->getCars();
        $this->assertCount(1, $cars2);
        $this->assertInstanceOf(GasolineCar::class, $cars2[0]);
        $this->assertEquals('Chevrolet', $cars2[0]->getBrand());
        $this->assertEquals('regular', $cars2[0]->getGasolineKind());
    }

    public function testExpressionLanguageFeatures(): void
    {
        new DataMapper(new \Kassko\DataMapper\ServiceResolver());
        
        $garage = new Garage(1);
        
        // Test that ##object and #id work in DataSource arguments
        // The DataSource receives #id which references the garage's id property
        $cars = $garage->getCars();
        $this->assertNotEmpty($cars, 'DataSource with #id expression should work');
        
        // Test that rawDataItemExists() works in discriminators
        // Each car is resolved based on presence of gasolineKind or energyProvider fields
        $hasGasolineCar = false;
        $hasElectricCar = false;
        
        foreach ($cars as $car) {
            if ($car instanceof GasolineCar) {
                $hasGasolineCar = true;
            }
            if ($car instanceof ElectricCar) {
                $hasElectricCar = true;
            }
        }
        
        $this->assertTrue($hasGasolineCar, 'rawDataItemExists("gasolineKind") should match gasoline cars');
        $this->assertTrue($hasElectricCar, 'rawDataItemExists("energyProvider") should match electric cars');
    }
}
