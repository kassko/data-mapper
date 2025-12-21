<?php

declare(strict_types=1);

namespace Kassko\Sample;

class ShopDataSource
{
    public function getShopsSurveyInfo(): array
    {
        return [
            'bestShop' => ['name' => 'The best', 'address' => 'Street of the best'],
            'worstShop' => ['name' => 'The worst', 'address' => 'Street of the worst'],
        ];
    }
}
