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

class ApiService
{
    public function getProductName(int $id): string
    {
        return "Fresh API Product $id";
    }

    public function getProductPrice(int $id): float
    {
        return 149.99;
    }

    public function getUserData(int $userId): array
    {
        return [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john.doe@example.com',
        ];
    }

    public function getConfig(): array
    {
        return [
            'theme' => 'light',
            'language' => 'fr',
            'notifications' => true,
        ];
    }

    public function getProductData(int $id): array
    {
        return [
            'name' => "Fresh API Product $id",
            'price' => 149.99,
        ];
    }
}
