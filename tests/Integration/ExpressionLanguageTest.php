<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Tests\Integration;

use Kassko\DataMapper\DataMapper;
use Kassko\Sample\PersonWithCar;
use PHPUnit\Framework\TestCase;

class ExpressionLanguageTest extends TestCase
{
    public function testSimplePropertyReferenceExpression(): void
    {
        $dataMapper = new DataMapper();
        $person = new PersonWithCar(1);

        $dataMapper->prepare($person);

        // The personSource uses #id which references $id property
        $name = $person->getName();
        $this->assertEquals('foo', $name);
    }

    public function testExprWithSourceFunction(): void
    {
        $dataMapper = new DataMapper();
        $person = new PersonWithCar(1);

        $dataMapper->prepare($person);

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
        $dataMapper = new DataMapper();
        $person = new PersonWithCar(2);

        $dataMapper->prepare($person);

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
        $dataMapper = new DataMapper();
        $person = new PersonWithCar(3);

        $dataMapper->prepare($person);

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
        $dataMapper = new DataMapper();
        
        $person1 = new PersonWithCar(1);
        $person2 = new PersonWithCar(2);
        
        $dataMapper->prepare($person1);
        $dataMapper->prepare($person2);
        
        $car1 = $person1->getCar();
        $car2 = $person2->getCar();
        
        $this->assertEquals('Toyota', $car1->brand);
        $this->assertEquals('Honda', $car2->brand);
    }
}
