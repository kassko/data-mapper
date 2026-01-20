<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Tests\Integration\Attributes\PropertyCheckItemClass\Fixtures;

use Kassko\DataMapper\Attribute\Property;

class PersonWithTypehintFallback
{
    private ?string $firstName = null;
    private ?string $lastName = null;

    // Should use Address class from PHP typehint since Property::class is not set
    #[Property(sourceField: 'main_address')]
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
