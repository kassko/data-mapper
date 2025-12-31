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

/**
 * DataSource for PropertyNamePrecedence tests.
 * 
 * Returns data with both 'property_value' and 'config_value' keys.
 * The test verifies which key is used based on Property vs PropertyConfig precedence.
 */
class PropertyNameDataSource
{
    public function getItems(int $id): array
    {
        return match($id) {
            1 => [
                ['property_value' => 'Value from property', 'config_value' => 'Value from config'],
                ['property_value' => 'Value 2 from property', 'config_value' => 'Value 2 from config'],
            ],
            default => [],
        };
    }

    public function getSingleItem(int $id): array
    {
        return match($id) {
            1 => ['property_value' => 'Single value from property', 'config_value' => 'Single value from config'],
            default => [],
        };
    }
}
