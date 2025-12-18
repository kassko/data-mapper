<?php

declare(strict_types=1);

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
