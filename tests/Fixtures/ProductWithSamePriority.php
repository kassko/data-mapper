<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Tests\Fixtures;

use Kassko\DataMapper\Attribute\DataSourceRef;
use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\Attribute\MultiPropDataSource;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

#[DataSourcesStore([
    new MultiPropDataSource(
        id: 'defaultData',
        class: DefaultsService::class,
        method: 'getProductData',
        args: ['#id'],
        priority: 5
    ),
    new MultiPropDataSource(
        id: 'cacheData',
        class: CacheService::class,
        method: 'getProductData',
        args: ['#id'],
        priority: 5
    ),
    new MultiPropDataSource(
        id: 'apiData',
        class: ApiService::class,
        method: 'getProductData',
        args: ['#id'],
        priority: 5
    ),
])]
class ProductWithSamePriority
{
    use LoadableTrait;

    private int $id = 1;

    // All three sources with same priority - last in providers list should win
    #[DataSourceRef(providers: ['defaultData', 'cacheData', 'apiData'])]
    private ?string $name = null;

    public function getName(): ?string
    {
        $this->loadProperty('name');
        return $this->name;
    }
}
