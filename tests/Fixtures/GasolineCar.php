<?php

declare(strict_types=1);

namespace Kassko\Sample;

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
