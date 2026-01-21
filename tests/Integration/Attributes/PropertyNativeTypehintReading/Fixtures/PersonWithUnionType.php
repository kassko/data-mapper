<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Tests\Integration\Attributes\PropertyNativeTypehintReading\Fixtures;

/**
 * Person class with union type - should use the first non-null instantiable class
 */
class PersonWithUnionType
{
    private ?string $firstName = null;
    
    // Union type - should use Address (first instantiable class)
    private Address|AddressCollection|null $location = null;

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLocation(): Address|AddressCollection|null
    {
        return $this->location;
    }

    public function setLocation(Address|AddressCollection|null $location): self
    {
        $this->location = $location;
        return $this;
    }
}
