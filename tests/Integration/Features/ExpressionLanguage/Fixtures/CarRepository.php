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

namespace Kassko\Sample\ExpressionLanguage;

class CarRepository
{
    public function find(int $id): ?Car
    {
        return match($id) {
            100 => new Car(100, 'Toyota', 'Camry'),
            200 => new Car(200, 'Honda', 'Accord'),
            300 => new Car(300, 'Ford', 'Mustang'),
            default => null,
        };
    }
}
