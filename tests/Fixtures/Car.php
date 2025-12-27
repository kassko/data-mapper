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

class Car
{
    public function __construct(
        public readonly int $id,
        public readonly string $brand,
        public readonly string $model
    ) {
    }
}
