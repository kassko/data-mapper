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

class DefaultsService
{
    public function getDefaults(): array
    {
        return [
            'theme' => 'system',
            'language' => 'en',
            'timezone' => 'UTC',
            'notifications' => false,
        ];
    }

    public function getProductName(int $id): string
    {
        return "Default Product";
    }

    public function getProductData(int $id): array
    {
        return [
            'name' => "Default Product",
        ];
    }
}
