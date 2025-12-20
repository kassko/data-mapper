<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Tests\Integration;

use Kassko\DataMapper\Attribute\MultiPropDataSource;
use Kassko\DataMapper\Attribute\DataSourceRef;
use Kassko\DataMapper\DataMapper;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;
use Kassko\Sample\PersonDataSource;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class ServiceLocatorTest extends TestCase
{
    public function testServiceLocatorWithContainerPrefix(): void
    {
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

        // Create an entity that uses service locator pattern
        $entity = new 
        #[MultiPropDataSource(id: 'personData', class: '@person.data_source', method: 'getData', args: ['#id'])]
        class(1) {
            use LoadableTrait;

            private int $id;

            #[DataSourceRef(id: 'personData')]
            private ?string $name = null;

            #[DataSourceRef(id: 'personData')]
            private ?string $email = null;

            public function __construct(int $id)
            {
                $this->id = $id;
            }

            public function getName(): ?string
            {
                $this->loadProperty('name');
                return $this->name;
            }

            public function getEmail(): ?string
            {
                $this->loadProperty('email');
                return $this->email;
            }
        };

        new DataMapper($container);

        $this->assertEquals('foo', $entity->getName());
        $this->assertEquals('foo@aaa.com', $entity->getEmail());
        
        \Kassko\DataMapper\Registry\LazyLoaderRegistry::clear();
    }

    public function testServiceLocatorThrowsExceptionWithoutContainer(): void
    {
        // Create an entity that uses service locator pattern
        $entity = new class(1) {
            use LoadableTrait;

            private int $id;

            #[DataSource(class: '@person.data_source', method: 'getData', args: ['#id'])]
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

        new DataMapper(); // No container provided

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot resolve service identifier');

        $entity->getName();
        
        \Kassko\DataMapper\Registry\LazyLoaderRegistry::clear();
    }
}
