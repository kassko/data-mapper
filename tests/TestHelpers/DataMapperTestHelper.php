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
use Kassko\DataMapper\Loader\Loader;
use Kassko\DataMapper\ServiceResolver;

trait DataMapperTestHelper
{
    /**
     * Create a DataMapper with an empty ServiceResolver
     */
    protected function createDataMapper(): DataMapper
    {
        return new DataMapper(new ServiceResolver());
    }

    /**
     * Create a DataMapper with a custom ServiceResolver
     */
    protected function createDataMapperWithResolver(ServiceResolver $serviceResolver): DataMapper
    {
        return new DataMapper($serviceResolver);
    }

    /**
     * Create a DataMapper with a service locator
     */
    protected function createDataMapperWithServices(array $services): DataMapper
    {
        $locator = new ArrayServiceLocator($services);
        $resolver = new ServiceResolver($locator);
        return new DataMapper($resolver);
    }

    /**
     * Create a Loader with an empty ServiceResolver
     */
    protected function createLoader(): Loader
    {
        return new Loader(new ServiceResolver());
    }

    /**
     * Create a Loader with a custom ServiceResolver
     */
    protected function createLoaderWithResolver(ServiceResolver $serviceResolver): Loader
    {
        return new Loader($serviceResolver);
    }

    /**
     * Create a Loader with a service locator
     */
    protected function createLoaderWithServices(array $services): Loader
    {
        $locator = new ArrayServiceLocator($services);
        $resolver = new ServiceResolver($locator);
        return new Loader($resolver);
    }
}
