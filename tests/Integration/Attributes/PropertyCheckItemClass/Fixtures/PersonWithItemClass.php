<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Tests\Integration\Attributes\PropertyCheckItemClass\Fixtures;

use Kassko\DataMapper\Attribute\Property;

class PersonWithItemClass
{
    private ?string $firstName = null;
    private ?string $lastName = null;

    #[Property(itemClass: Address::class)]
    private ?array $addresses = null;

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

    public function getAddresses(): ?array
    {
        return $this->addresses;
    }

    public function setAddresses(?array $addresses): self
    {
        $this->addresses = $addresses;
        return $this;
    }
}
