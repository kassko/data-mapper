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

namespace Kassko\Sample\UseCases;

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
