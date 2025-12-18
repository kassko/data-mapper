<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Tests\Unit;

use Kassko\DataMapper\ServiceResolver;
use Kassko\DataMapper\ArrayServiceLocator;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

final class ServiceResolverTest extends TestCase
{
    public function testResolveDirectContainerLookup(): void
    {
        $service = new \stdClass();
        
        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')->with('person.datasource')->willReturn(true);
        $container->method('get')->with('person.datasource')->willReturn($service);

        $resolver = new ServiceResolver($container, []);
        
        $this->assertSame($service, $resolver->resolve('@person.datasource'));
    }

    public function testResolveViaLocator(): void
    {
        $locator = new ArrayServiceLocator([
            'Kassko\Sample\PersonDataSource' => '@person.data_source',
        ]);

        $service = new \stdClass();
        
        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')->with('person.data_source')->willReturn(true);
        $container->method('get')->with('person.data_source')->willReturn($service);

        $resolver = new ServiceResolver($container, [$locator]);
        
        $this->assertSame($service, $resolver->resolve('Kassko\Sample\PersonDataSource'));
    }

    public function testResolveDirectInstantiation(): void
    {
        $resolver = new ServiceResolver(null, []);
        
        $instance = $resolver->resolve(\stdClass::class);
        
        $this->assertInstanceOf(\stdClass::class, $instance);
    }

    public function testThrowsWhenNoContainerForServiceId(): void
    {
        $resolver = new ServiceResolver(null, []);
        
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot resolve service identifier');
        
        $resolver->resolve('@some.service');
    }

    public function testThrowsWhenClassDoesNotExist(): void
    {
        $resolver = new ServiceResolver(null, []);
        
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("DataSource class 'NonExistentClass' does not exist");
        
        $resolver->resolve('NonExistentClass');
    }

    public function testThrowsWhenServiceNotFoundInContainer(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')->with('unknown.service')->willReturn(false);

        $resolver = new ServiceResolver($container, []);
        
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Service "unknown.service" not found in container');
        
        $resolver->resolve('@unknown.service');
    }

    public function testResolveViaMultipleLocators(): void
    {
        $familyLocator = new ArrayServiceLocator([
            'Kassko\Sample\PersonDataSource' => '@person.data_source',
        ]);

        $vehicleLocator = new ArrayServiceLocator([
            'Kassko\Sample\CarRepository' => '@car.repository',
        ]);

        $personService = new \stdClass();
        $carService = new \stdClass();
        
        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')->willReturnCallback(
            fn($id) => in_array($id, ['person.data_source', 'car.repository'])
        );
        $container->method('get')->willReturnCallback(function($id) use ($personService, $carService) {
            return match($id) {
                'person.data_source' => $personService,
                'car.repository' => $carService,
            };
        });

        $resolver = new ServiceResolver($container, [$familyLocator, $vehicleLocator]);
        
        $this->assertSame($personService, $resolver->resolve('Kassko\Sample\PersonDataSource'));
        $this->assertSame($carService, $resolver->resolve('Kassko\Sample\CarRepository'));
    }

    public function testResolveLocatorReturnsClassName(): void
    {
        $locator = new ArrayServiceLocator([
            'DIPLOMA' => \stdClass::class,
        ]);

        $resolver = new ServiceResolver(null, [$locator]);
        
        $instance = $resolver->resolve('DIPLOMA');
        
        $this->assertInstanceOf(\stdClass::class, $instance);
    }
}
