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

namespace Kassko\Sample\Features\RecursiveHydration;

use Kassko\DataMapper\Attribute\MultiPropDataSource;
use Kassko\DataMapper\Attribute\DataSourceRef;
use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\Attribute\Property;
use Kassko\DataMapper\Attribute\Loading;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

#[DataSourcesStore([
    new MultiPropDataSource(
        id: 'infoSource',
        class: ShopDataSource::class,
        method: 'getShopsSurveyInfo',
    )
])]
class Information
{
    use LoadableTrait;

    #[DataSourceRef(id: 'infoSource')]
    #[Property(class: Shop::class)]
    #[Loading(type: Loading::TYPE_EAGER, depth: 2)]
    private ?Shop $bestShop = null;

    #[DataSourceRef(id: 'infoSource')]
    #[Property(class: Shop::class)]
    #[Loading(depth: 2)]
    private ?Shop $worstShop = null;

    public function getBestShop(): ?Shop
    {
        $this->loadProperty('bestShop');
        return $this->bestShop;
    }

    public function getWorstShop(): ?Shop
    {
        $this->loadProperty('worstShop');
        return $this->worstShop;
    }
}
