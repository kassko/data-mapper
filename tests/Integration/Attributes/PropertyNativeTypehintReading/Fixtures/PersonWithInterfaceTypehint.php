<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Tests\Integration\Attributes\PropertyNativeTypehintReading\Fixtures;

/**
 * Person class with interface typehint - should NOT trigger synthetic property creation
 * because interfaces are not instantiable
 */
class PersonWithInterfaceTypehint
{
    private ?string $firstName = null;
    
    // Interface typehint - should NOT create synthetic Property (not instantiable)
    private ?AddressableInterface $location = null;

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLocation(): ?AddressableInterface
    {
        return $this->location;
    }

    public function setLocation(?AddressableInterface $location): self
    {
        $this->location = $location;
        return $this;
    }
}
