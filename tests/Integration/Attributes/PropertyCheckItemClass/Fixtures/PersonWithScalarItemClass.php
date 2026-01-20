<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Tests\Integration\Attributes\PropertyCheckItemClass\Fixtures;

use Kassko\DataMapper\Attribute\Property;

class PersonWithScalarItemClass
{
    private ?string $firstName = null;

    // This should throw an exception - itemClass cannot be used with scalar types
    #[Property(itemClass: Address::class)]
    private ?string $invalidProperty = null;

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getInvalidProperty(): ?string
    {
        return $this->invalidProperty;
    }

    public function setInvalidProperty(?string $invalidProperty): self
    {
        $this->invalidProperty = $invalidProperty;
        return $this;
    }
}
