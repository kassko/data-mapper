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

namespace Kassko\Sample\Portable;

class PersonDataSource
{
    public function getData(int $id): array
    {
        return match($id) {
            1 => ['name' => 'foo', 'email' => 'foo@aaa.com'],
            2 => ['name' => 'bar', 'email' => 'bar@bbb.com'],
            default => ['name' => 'baz', 'email' => 'baz@ccc.com'],
        };
    }
}
