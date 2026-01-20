<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Tests\Integration\Attributes\PropertyCheckItemClass\Fixtures;

use Kassko\DataMapper\Attribute\Property;

class PersonWithClassForSingleObject
{
    private ?string $firstName = null;
    private ?string $lastName = null;

    #[Property(class: Address::class)]
    private ?Address $address = null;

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

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(?Address $address): self
    {
        $this->address = $address;
        return $this;
    }
}
