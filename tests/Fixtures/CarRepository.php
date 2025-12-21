<?php

declare(strict_types=1);

namespace Kassko\Sample;

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
