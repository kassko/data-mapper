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
use Kassko\Sample\PersonWithCar;
use PHPUnit\Framework\TestCase;

class ExpressionLanguageTest extends TestCase
{
    protected function tearDown(): void
    {
        LoaderRegistry::clear();
    }

    public function testSimplePropertyReferenceExpression(): void
    {
        new DataMapper(new \Kassko\DataMapper\ServiceResolver());
        $person = new PersonWithCar(1);

        // The personSource uses #id which references $id property
        $name = $person->getName();
        $this->assertEquals('foo', $name);
    }

    public function testExprWithSourceFunction(): void
    {
        new DataMapper(new \Kassko\DataMapper\ServiceResolver());
        $person = new PersonWithCar(1);

        // The carSource uses expr(source('personSource')['car_id'])
        // This should load personSource, extract car_id, and use it to load the car
        $car = $person->getCar();
        
        $this->assertNotNull($car);
        $this->assertEquals(100, $car->id);
        $this->assertEquals('Toyota', $car->brand);
        $this->assertEquals('Camry', $car->model);
    }

    public function testDependencyChainResolution(): void
    {
        new DataMapper(new \Kassko\DataMapper\ServiceResolver());
        $person = new PersonWithCar(2);

        // Loading car requires loading personSource first (dependency)
        $car = $person->getCar();
        
        $this->assertNotNull($car);
        $this->assertEquals(200, $car->id);
        $this->assertEquals('Honda', $car->brand);
        
        // PersonSource should also have been loaded
        $this->assertEquals('bar', $person->getName());
        $this->assertEquals('Bar', $person->getFirstName());
    }

    public function testSourceResultIsCached(): void
    {
        new DataMapper(new \Kassko\DataMapper\ServiceResolver());
        $person = new PersonWithCar(3);

        // Load car first (which loads personSource internally)
        $car = $person->getCar();
        $this->assertNotNull($car);
        $this->assertEquals(300, $car->id);
        
        // Now accessing name should not trigger another personSource call
        // because it was already loaded when resolving car
        $name = $person->getName();
        $this->assertEquals('baz', $name);
        
        $firstName = $person->getFirstName();
        $this->assertEquals('Baz', $firstName);
    }

    public function testMultipleExpressionEvaluations(): void
    {
        new DataMapper(new \Kassko\DataMapper\ServiceResolver());
        
        $person1 = new PersonWithCar(1);
        $person2 = new PersonWithCar(2);
        
        $car1 = $person1->getCar();
        $car2 = $person2->getCar();
        
        $this->assertEquals('Toyota', $car1->brand);
        $this->assertEquals('Honda', $car2->brand);
    }
}
