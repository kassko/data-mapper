<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Tests\Integration\Attributes\PropertyNativeTypehintReading\Fixtures;

use Kassko\DataMapper\Attribute as DM;

/**
 * Person class WITH explicit Property attribute on addresses property.
 * This is the expected working case - used for comparison.
 */
class PersonWithExplicitProperty
{
    private ?string $firstName = null;
    private ?string $lastName = null;
    
    #[DM\Property(class: AddressCollection::class)]
    private ?AddressCollection $addresses = null;

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

    public function getAddresses(): ?AddressCollection
    {
        return $this->addresses;
    }

    public function setAddresses(?AddressCollection $addresses): self
    {
        $this->addresses = $addresses;
        return $this;
    }
}
