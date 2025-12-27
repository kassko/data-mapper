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

class PersonFullDataSource
{
    public function getFullData(int $id): array
    {
        $data = [
            1 => ['first_name' => 'John', 'last_name' => 'Doe', 'email' => 'john@example.com', 'phone' => '123-456-7890', 'age' => 30],
            2 => ['first_name' => 'Jane', 'last_name' => 'Smith', 'email' => 'jane@example.com', 'phone' => '098-765-4321', 'age' => 25],
        ];
        
        return $data[$id] ?? [];
    }
}
