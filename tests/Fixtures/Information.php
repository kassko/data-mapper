<?php

declare(strict_types=1);

namespace Kassko\Sample;

use Kassko\DataMapper\Attribute\MultiPropDataSource;
use Kassko\DataMapper\Attribute\DataSourceRef;
use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\Attribute\Property;
use Kassko\DataMapper\Attribute\Loading;
use Kassko\DataMapper\ObjectExtension\LoadableInternalTrait;

#[DataSourcesStore([
    new MultiPropDataSource(
        id: 'infoSource',
        class: ShopDataSource::class,
        method: 'getShopsSurveyInfo',
    )
])]
class Information
{
    use LoadableInternalTrait;

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
