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

use Kassko\DataMapper\Attribute\MultiPropDataSource;
use Kassko\DataMapper\Attribute\DataSourceRef;
use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\ArrayServiceLocator;
use Kassko\DataMapper\DataMapper;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;
use Kassko\DataMapper\ServiceResolver;
use Kassko\Sample\PersonDataSource;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class ServiceLocatorTest extends TestCase
{
    public function testServiceLocatorWithContainerPrefix(): void
    {
        // Create a service locator
        $locator = new ArrayServiceLocator([
            'person.data_source' => new PersonDataSource()
        ]);

        // Create an entity that uses service locator pattern
        $entity = new 
        #[DataSourcesStore([
            new MultiPropDataSource(id: 'personData', class: '@person.data_source', method: 'getData', args: ['#id'])
        ])]
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

        new DataMapper(new ServiceResolver($locator));

        $this->assertEquals('foo', $entity->getName());
        $this->assertEquals('foo@aaa.com', $entity->getEmail());
        
        \Kassko\DataMapper\Registry\LoaderRegistry::clear();
    }

    public function testServiceLocatorThrowsExceptionWithoutContainer(): void
    {
        // Create an entity that uses service locator pattern
        $entity = new 
        #[DataSourcesStore([
            new MultiPropDataSource(id: 'personData', class: '@person.data_source', method: 'getData', args: ['#id'])
        ])]
        class(1) {
            use LoadableTrait;

            private int $id;

            #[DataSourceRef(id: 'personData')]
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

        new DataMapper(new \Kassko\DataMapper\ServiceResolver()); // No container provided

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot resolve service identifier');

        $entity->getName();
        
        \Kassko\DataMapper\Registry\LoaderRegistry::clear();
    }
}
