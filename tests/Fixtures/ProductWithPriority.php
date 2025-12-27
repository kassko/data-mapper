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

namespace Kassko\DataMapper\Tests\Fixtures;

use Kassko\DataMapper\Attribute\DataSourceRef;
use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\Attribute\MultiPropDataSource;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

#[DataSourcesStore([
    // Cache with low priority
    new MultiPropDataSource(
        id: 'cacheData',
        class: CacheService::class,
        method: 'getProductData',
        args: ['#id'],
        priority: 0
    ),
    // API with high priority - will override cache
    new MultiPropDataSource(
        id: 'apiData',
        class: ApiService::class,
        method: 'getProductData',
        args: ['#id'],
        priority: 10
    ),
])]
class ProductWithPriority
{
    use LoadableTrait;

    private int $id = 1;

    // Aggregate both sources - higher priority (API) will win
    #[DataSourceRef(providers: ['cacheData', 'apiData'])]
    private ?string $name = null;

    #[DataSourceRef(providers: ['cacheData', 'apiData'])]
    private ?float $price = null;

    public function getName(): ?string
    {
        $this->loadProperty('name');
        return $this->name;
    }

    public function getPrice(): ?float
    {
        $this->loadProperty('price');
        return $this->price;
    }
}
