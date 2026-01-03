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

namespace Kassko\Sample\Hook;

class GasolineCar extends Vehicle
{
    private ?string $gasolineKind = null;

    public function getGasolineKind(): ?string
    {
        return $this->gasolineKind;
    }

    public function setGasolineKind(?string $gasolineKind): void
    {
        $this->gasolineKind = $gasolineKind;
    }
}
