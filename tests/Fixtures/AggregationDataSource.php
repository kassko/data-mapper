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

class AggregationDataSource
{
    public function providerA(): array
    {
        return ['name' => 'Alice', 'age' => 25];
    }
    
    public function providerB(): array
    {
        return ['age' => 30, 'city' => 'Paris'];
    }
    
    public function providerC(): array
    {
        return ['city' => 'London'];
    }
    
    public function providerNestedA(): array
    {
        return ['config' => ['db' => ['host' => 'localhost', 'port' => 3306]]];
    }
    
    public function providerNestedB(): array
    {
        return ['config' => ['db' => ['port' => 5432, 'user' => 'admin']]];
    }
}
