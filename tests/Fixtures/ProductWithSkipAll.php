<?php

declare(strict_types=1);

namespace Kassko\Sample;

use Kassko\DataMapper\Attribute\SkipAllProperties;
use Kassko\DataMapper\Attribute\Property;

#[SkipAllProperties]
class ProductWithSkipAll
{
    private ?string $name = null;
    
    #[Property]
    private ?string $description = null;
    
    private ?float $price = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): void
    {
        $this->price = $price;
    }
}
