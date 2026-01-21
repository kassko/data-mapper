<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Tests\Integration\Attributes\PropertyNativeTypehintReading\Fixtures;

/**
 * Simple Address class for testing native typehint reading
 */
class Address
{
    private ?string $street = null;
    private ?string $city = null;
    private ?string $postalCode = null;

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): self
    {
        $this->street = $street;
        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;
        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): self
    {
        $this->postalCode = $postalCode;
        return $this;
    }
}
