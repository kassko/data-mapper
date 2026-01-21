<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Tests\Integration\Attributes\PropertyNativeTypehintReading\Fixtures;

/**
 * Person class with abstract class typehint - should NOT trigger synthetic property creation
 * because abstract classes are not instantiable
 */
class PersonWithAbstractTypehint
{
    private ?string $firstName = null;
    
    // Abstract class typehint - should NOT create synthetic Property (not instantiable)
    private ?AbstractLocation $location = null;

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLocation(): ?AbstractLocation
    {
        return $this->location;
    }

    public function setLocation(?AbstractLocation $location): self
    {
        $this->location = $location;
        return $this;
    }
}
