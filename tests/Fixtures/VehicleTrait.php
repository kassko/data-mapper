<?php

declare(strict_types=1);

namespace Kassko\Sample;

trait VehicleTrait
{
    private ?string $model = null;

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(?string $model): void
    {
        $this->model = $model;
    }
}
