<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Tests\Integration;

use Kassko\DataMapper\DataMapperBuilder;
use Kassko\DataMapper\ArrayServiceLocator;
use Kassko\DataMapper\Attribute\DataSource;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;
use Kassko\Sample\PersonDataSource;
use Kassko\Sample\CarRepository;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

final class ServiceLocatorIntegrationTest extends TestCase
{
    public function testDirectContainerResolution(): void
    {
        // Create a mock container
        $container = new class implements ContainerInterface {
            public function get(string $id): object
            {
                if ($id === 'person.datasource') {
                    return new PersonDataSource();
                }
                throw new \RuntimeException("Service not found: {$id}");
            }

            public function has(string $id): bool
            {
                return $id === 'person.datasource';
            }
        };

        // Create an entity that uses @service.id pattern
        $entity = new class(1) {
            use LoadableTrait;

            private int $id;

            #[DataSource(class: '@person.datasource', method: 'getData', args: ['#id'])]
            private ?string $name = null;

            public function __construct(int $id)
            {
                $this->id = $id;
            }

            public function getName(): ?string
            {
                $this->loadProperty('name');
                return $this->name;
            }
        };

        $dataMapper = (new DataMapperBuilder())
            ->setContainer($container)
            ->build();

        $dataMapper->prepare($entity);

        $this->assertEquals('foo', $entity->getName());
    }

    public function testLocatorResolutionWithFQCN(): void
    {
        // Create locator mapping FQCN to service ID
        $locator = new ArrayServiceLocator([
            PersonDataSource::class => '@person.data_source',
        ]);

        // Create a mock container
        $container = new class implements ContainerInterface {
            public function get(string $id): object
            {
                if ($id === 'person.data_source') {
                    return new PersonDataSource();
                }
                throw new \RuntimeException("Service not found: {$id}");
            }

            public function has(string $id): bool
            {
                return $id === 'person.data_source';
            }
        };

        // Create an entity using FQCN in DataSource
        $entity = new class(2) {
            use LoadableTrait;

            private int $id;

            #[DataSource(class: PersonDataSource::class, method: 'getData', args: ['#id'])]
            private ?string $name = null;

            public function __construct(int $id)
            {
                $this->id = $id;
            }

            public function getName(): ?string
            {
                $this->loadProperty('name');
                return $this->name;
            }
        };

        $dataMapper = (new DataMapperBuilder())
            ->setContainer($container)
            ->addLocator($locator)
            ->build();

        $dataMapper->prepare($entity);

        $this->assertEquals('bar', $entity->getName());
    }

    public function testDirectInstantiationWithNoContainerOrLocator(): void
    {
        // Create an entity using direct class reference
        $entity = new class(3) {
            use LoadableTrait;

            private int $id;

            #[DataSource(class: PersonDataSource::class, method: 'getData', args: ['#id'])]
            private ?string $name = null;

            public function __construct(int $id)
            {
                $this->id = $id;
            }

            public function getName(): ?string
            {
                $this->loadProperty('name');
                return $this->name;
            }
        };

        // Build without container or locators - should instantiate directly
        $dataMapper = (new DataMapperBuilder())->build();

        $dataMapper->prepare($entity);

        $this->assertEquals('baz', $entity->getName());
    }

    public function testMultipleLocators(): void
    {
        $familyLocator = new ArrayServiceLocator([
            PersonDataSource::class => '@person.data_source',
        ]);

        $vehicleLocator = new ArrayServiceLocator([
            CarRepository::class => '@car.repository',
        ]);

        // Create a mock container
        $personService = new PersonDataSource();
        $carService = new CarRepository();
        
        $container = new class($personService, $carService) implements ContainerInterface {
            public function __construct(
                private PersonDataSource $personService,
                private CarRepository $carService
            ) {}
            
            public function get(string $id): object
            {
                return match($id) {
                    'person.data_source' => $this->personService,
                    'car.repository' => $this->carService,
                    default => throw new \RuntimeException("Service not found: {$id}")
                };
            }

            public function has(string $id): bool
            {
                return in_array($id, ['person.data_source', 'car.repository']);
            }
        };

        // Create an entity using PersonDataSource
        $person = new class(1) {
            use LoadableTrait;

            private int $id;

            #[DataSource(class: PersonDataSource::class, method: 'getData', args: ['#id'])]
            private ?string $name = null;

            public function __construct(int $id)
            {
                $this->id = $id;
            }

            public function getName(): ?string
            {
                $this->loadProperty('name');
                return $this->name;
            }
        };

        $dataMapper = (new DataMapperBuilder())
            ->setContainer($container)
            ->addLocator($familyLocator)
            ->addLocator($vehicleLocator)
            ->build();

        $dataMapper->prepare($person);

        $this->assertEquals('foo', $person->getName());
    }

    public function testSemanticKeyResolution(): void
    {
        // Create locator with semantic keys
        $proofLocator = new ArrayServiceLocator([
            'DIPLOMA' => PersonDataSource::class,  // Maps to a class that will be instantiated
        ]);

        // Create an entity using semantic key
        $entity = new class(1) {
            use LoadableTrait;

            private int $id;

            #[DataSource(class: 'DIPLOMA', method: 'getData', args: ['#id'])]
            private ?string $name = null;

            public function __construct(int $id)
            {
                $this->id = $id;
            }

            public function getName(): ?string
            {
                $this->loadProperty('name');
                return $this->name;
            }
        };

        $dataMapper = (new DataMapperBuilder())
            ->addLocator($proofLocator)
            ->build();

        $dataMapper->prepare($entity);

        $this->assertEquals('foo', $entity->getName());
    }

    public function testLocatorPriorityOrder(): void
    {
        // First locator
        $locator1 = new ArrayServiceLocator([
            'TEST_KEY' => PersonDataSource::class,
        ]);

        // Second locator with same key (should not be used)
        $locator2 = new ArrayServiceLocator([
            'TEST_KEY' => CarRepository::class,
        ]);

        // Create an entity
        $entity = new class(1) {
            use LoadableTrait;

            private int $id;

            #[DataSource(class: 'TEST_KEY', method: 'getData', args: ['#id'])]
            private ?string $name = null;

            public function __construct(int $id)
            {
                $this->id = $id;
            }

            public function getName(): ?string
            {
                $this->loadProperty('name');
                return $this->name;
            }
        };

        // First locator should take priority
        $dataMapper = (new DataMapperBuilder())
            ->addLocator($locator1)
            ->addLocator($locator2)
            ->build();

        $dataMapper->prepare($entity);

        $this->assertEquals('foo', $entity->getName());
    }

    public function testRecursiveResolution(): void
    {
        // Locator maps semantic key to service ID
        $locator = new ArrayServiceLocator([
            'MY_DATASOURCE' => '@person.data_source',
        ]);

        // Container resolves service ID
        $container = new class implements ContainerInterface {
            public function get(string $id): object
            {
                if ($id === 'person.data_source') {
                    return new PersonDataSource();
                }
                throw new \RuntimeException("Service not found: {$id}");
            }

            public function has(string $id): bool
            {
                return $id === 'person.data_source';
            }
        };

        // Create an entity using semantic key
        $entity = new class(1) {
            use LoadableTrait;

            private int $id;

            #[DataSource(class: 'MY_DATASOURCE', method: 'getData', args: ['#id'])]
            private ?string $name = null;

            public function __construct(int $id)
            {
                $this->id = $id;
            }

            public function getName(): ?string
            {
                $this->loadProperty('name');
                return $this->name;
            }
        };

        $dataMapper = (new DataMapperBuilder())
            ->setContainer($container)
            ->addLocator($locator)
            ->build();

        $dataMapper->prepare($entity);

        // Should resolve: MY_DATASOURCE -> @person.data_source -> PersonDataSource instance
        $this->assertEquals('foo', $entity->getName());
    }
}
