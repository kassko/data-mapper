<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Tests\Integration\Attributes\PropertyNativeTypehintReading\Fixtures;

/**
 * Person class WITHOUT explicit Property attribute on addresses property.
 * The hydrator should use the native PHP typehint (AddressCollection) to hydrate the property.
 *
 * This is the bug reproduction case:
 * - Before fix: The raw array data was passed directly to setAddresses(), causing a type error
 * - After fix: The hydrator reads the native typehint and creates an AddressCollection instance
 */
class PersonWithNativeTypehint
{
    private ?string $firstName = null;
    private ?string $lastName = null;
    
    // No #[Property] attribute - should use native typehint AddressCollection
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
