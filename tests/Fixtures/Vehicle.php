<?php

declare(strict_types=1);

namespace Kassko\Sample;

abstract class Vehicle
{
    private ?int $id = null;
    private ?string $brand = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(?string $brand): void
    {
        $this->brand = $brand;
    }
}
