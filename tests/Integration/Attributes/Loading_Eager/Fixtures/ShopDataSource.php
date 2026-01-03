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

namespace Kassko\Sample\LoadingEager;

class ShopDataSource
{
    public function getShopsSurveyInfo(): array
    {
        return [
            'bestShop' => [
                'name' => 'The best',
                'address' => '123 Main St',
            ],
            'worstShop' => [
                'name' => 'The worst',
                'address' => '456 Bad St',
            ],
        ];
    }
}
