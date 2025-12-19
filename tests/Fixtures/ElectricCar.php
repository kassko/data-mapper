<?php

declare(strict_types=1);

namespace Kassko\Sample;

class ElectricCar extends Vehicle
{
    private ?string $energyProvider = null;

    public function getEnergyProvider(): ?string
    {
        return $this->energyProvider;
    }

    public function setEnergyProvider(?string $energyProvider): void
    {
        $this->energyProvider = $energyProvider;
    }
}
