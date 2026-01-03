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

namespace Kassko\DataMapper\Tests\TestHelpers;

use Kassko\DataMapper\ArrayServiceLocator;
use Kassko\DataMapper\DataMapper;
use Kassko\DataMapper\DataMapperBuilder;
use Kassko\DataMapper\Registry\LoaderRegistry;
use Psr\Container\ContainerInterface;

/**
 * Native PHP implementation of DataMapperProviderInterface.
 * 
 * This provider creates DataMapper instances using the standard
 * PHP-native approach with DataMapperBuilder.
 */
class NativeDataMapperProvider implements DataMapperProviderInterface
{
    private ?DataMapper $dataMapper = null;

    public function getDataMapper(array $services = [], array $config = []): DataMapper
    {
        $builder = new DataMapperBuilder();

        // Register services via ArrayServiceLocator if provided
        if (!empty($services)) {
            $locator = new ArrayServiceLocator($services);
            $builder->addServiceLocator($locator);
        }

        // Note: Lineage collection is always enabled by default in DataMapper
        // The DataLineageCollector is automatically instantiated in DataMapper constructor

        if (isset($config['custom_hydrators']) && is_array($config['custom_hydrators'])) {
            foreach ($config['custom_hydrators'] as $name => $hydrator) {
                $builder->addCustomHydrator($name, $hydrator);
            }
        }

        if (isset($config['container']) && $config['container'] instanceof ContainerInterface) {
            $builder->setContainer($config['container']);
        }

        $this->dataMapper = $builder->build();

        return $this->dataMapper;
    }

    public function getContainer(): ?ContainerInterface
    {
        // Native provider doesn't have a container
        return null;
    }

    public function tearDown(): void
    {
        LoaderRegistry::clear();
        $this->dataMapper = null;
    }

    public function getName(): string
    {
        return 'native';
    }
}
