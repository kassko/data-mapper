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

class CacheService
{
    public function getProductName(int $id): string
    {
        return "Cached Product $id";
    }

    public function getProductPrice(int $id): float
    {
        return 99.99;
    }

    public function getUserData(int $userId): array
    {
        return [
            'firstName' => 'Cached',
            'lastName' => 'User',
            'email' => 'cached@example.com',
        ];
    }

    public function getConfig(): array
    {
        return [
            'theme' => 'dark',
            'language' => 'en',
            'timezone' => 'UTC',
        ];
    }

    public function getProductData(int $id): array
    {
        return [
            'name' => "Cached Product $id",
            'price' => 99.99,
        ];
    }
}
