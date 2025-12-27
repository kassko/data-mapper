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
