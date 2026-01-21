<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Tests\Integration\Attributes\PropertyNativeTypehintReading\Fixtures;

/**
 * Person class with native typehint for a single Address object (not a collection)
 */
class PersonWithSingleAddress
{
    private ?string $firstName = null;
    private ?string $lastName = null;
    
    // No #[Property] attribute - should use native typehint Address
    private ?Address $mainAddress = null;

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getMainAddress(): ?Address
    {
        return $this->mainAddress;
    }

    public function setMainAddress(?Address $mainAddress): self
    {
        $this->mainAddress = $mainAddress;
        return $this;
    }
}
