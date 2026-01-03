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

namespace Kassko\Sample\SinglePropDataSource;

/**
 * Data source for Product entity.
 */
class ProductDataSource
{
    private static array $products = [
        1 => ['name' => 'Laptop', 'price' => 999.99, 'description' => 'High performance laptop'],
        2 => ['name' => 'Mouse', 'price' => 29.99, 'description' => 'Wireless gaming mouse'],
        3 => ['name' => 'Keyboard', 'price' => 79.99, 'description' => 'Mechanical keyboard'],
    ];

    public function getName(int $id): ?string
    {
        return self::$products[$id]['name'] ?? null;
    }

    public function getPrice(int $id): ?float
    {
        return self::$products[$id]['price'] ?? null;
    }

    public function getDescription(int $id): ?string
    {
        return self::$products[$id]['description'] ?? null;
    }
}
