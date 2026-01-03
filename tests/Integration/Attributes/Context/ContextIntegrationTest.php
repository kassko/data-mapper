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

use Kassko\DataMapper\ArrayServiceLocator;
use Kassko\DataMapper\Attribute\Context;
use Kassko\DataMapper\Attribute\DataSourceRef;
use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\Attribute\MultiPropDataSource;
use Kassko\DataMapper\DataMapper;
use Kassko\DataMapper\DataMapperBuilder;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;
use Kassko\DataMapper\Registry\ContextRegistry;
use Kassko\DataMapper\Registry\LoaderRegistry;
use Kassko\DataMapper\ServiceResolver;
use PHPUnit\Framework\TestCase;

class ContextIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        ContextRegistry::clear();
        LoaderRegistry::clear();
    }

    protected function tearDown(): void
    {
        ContextRegistry::clear();
        LoaderRegistry::clear();
    }

    public function testContextWithNamedArguments(): void
    {
        $dataSource = new class {
            public function getData(): array
            {
                return ['name' => 'John', 'role' => 'admin'];
            }
        };

        $serviceLocator = new ArrayServiceLocator([
            'TestSource' => $dataSource,
        ]);

        $builder = new DataMapperBuilder();
        $builder->addLocator($serviceLocator);
        $builder->build();

        $testObject = new #[DataSourcesStore([
            new MultiPropDataSource(id: 'source', class: 'TestSource', method: 'getData'),
        ])]
        class {
            use LoadableTrait;

            #[Context(['key' => 'role', 'value' => 'admin'], ['key' => 'level', 'value' => 'high'])]
            #[DataSourceRef(id: 'source')]
            private ?string $name = null;

            public function getName(): ?string
            {
                $this->loadProperty('name');
                return $this->name;
            }
        };

        $testObject->getName();

        // Context values should be set
        $this->assertEquals('admin', ContextRegistry::get('role'));
        $this->assertEquals('high', ContextRegistry::get('level'));
    }

    public function testApplicationContextViaDataMapper(): void
    {
        $serviceResolver = new ServiceResolver();
        $dataMapper = new DataMapper($serviceResolver);

        // Add application context
        $dataMapper->addToContext('feature_flag', true);
        $dataMapper->addToContext('api_version', 'v2');

        // Verify context is accessible
        $this->assertTrue($dataMapper->hasContext('feature_flag'));
        $this->assertTrue($dataMapper->getContext('feature_flag'));
        $this->assertEquals('v2', $dataMapper->getContext('api_version'));

        // Add multiple at once
        $dataMapper->addManyToContext([
            'env' => 'production',
            'debug' => false,
        ]);

        $this->assertEquals('production', $dataMapper->getContext('env'));
        $this->assertFalse($dataMapper->getContext('debug'));
    }

    public function testHydrationContextOverridesApplicationContext(): void
    {
        // Set application context
        ContextRegistry::addToApplicationContext('mode', 'app_mode');

        // Set hydration context (should override)
        ContextRegistry::set('mode', 'hydration_mode');

        // Hydration context should take precedence
        $this->assertEquals('hydration_mode', ContextRegistry::get('mode'));

        // Clear hydration context
        ContextRegistry::clearHydrationContext();

        // Now should get application context
        $this->assertEquals('app_mode', ContextRegistry::get('mode'));
    }

    public function testContextKeyExistsExpression(): void
    {
        $dataSource = new class {
            public function getData(): array
            {
                return [
                    'items' => [
                        ['type' => 'a', 'value' => 'item1'],
                        ['type' => 'b', 'value' => 'item2'],
                    ]
                ];
            }
        };

        $serviceLocator = new ArrayServiceLocator([
            'TestSource' => $dataSource,
        ]);

        $builder = new DataMapperBuilder();
        $builder->addLocator($serviceLocator);
        $builder->build();

        // Set context to test contextKeyExists
        ContextRegistry::set('premium_user', true);

        $this->assertTrue(ContextRegistry::has('premium_user'));
        $this->assertFalse(ContextRegistry::has('non_existent_key'));
    }

    public function testDataLineageCollector(): void
    {
        $dataSource = new class {
            public function getData(): array
            {
                return ['name' => 'John'];
            }
        };

        $serviceLocator = new ArrayServiceLocator([
            'TestSource' => $dataSource,
        ]);

        $builder = new DataMapperBuilder();
        $builder->addLocator($serviceLocator);
        $dataMapper = $builder->build();

        // Enable lineage collection
        $dataMapper->enableLineageCollection();

        $testObject = new #[DataSourcesStore([
            new MultiPropDataSource(id: 'source', class: 'TestSource', method: 'getData'),
        ])]
        class {
            use LoadableTrait;

            #[DataSourceRef(id: 'source')]
            private ?string $name = null;

            public function getName(): ?string
            {
                $this->loadProperty('name');
                return $this->name;
            }
        };

        $testObject->getName();

        // Check that events were recorded
        $collector = $dataMapper->getLineageCollector();
        $events = $collector->getEvents();

        $this->assertNotEmpty($events);
    }
}
