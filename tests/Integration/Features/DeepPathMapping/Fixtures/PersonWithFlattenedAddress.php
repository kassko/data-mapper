<?php

declare(strict_types=1);

/*
 * This file is part of Data Mapper.
 *
 * Copyright 2025 kassko 
 *
 * For the full copyright and license information,
 * please view the LICENSE and NOTICE files that were distributed with this source code.
 */

namespace Kassko\DataMapper\Tests\Integration\Features\DeepPathMapping\Fixtures;

use Kassko\DataMapper\Attribute\Property;

/**
 * Domain object with 2-level deep path mapping from nested DTO.
 * 
 * Maps from PersonDto with nested AddressDto:
 * - firstName -> firstName
 * - lastName -> lastName  
 * - address.street -> street
 * - address.city -> city
 * - address.postalCode -> postalCode
 */
class PersonWithFlattenedAddress
{
    #[Property(sourceField: 'firstName')]
    private ?string $firstName = null;

    #[Property(sourceField: 'lastName')]
    private ?string $lastName = null;

    /**
     * Deep path: maps from address.street in the source.
     */
    #[Property(sourceField: 'address.street')]
    private ?string $street = null;

    /**
     * Deep path: maps from address.city in the source.
     */
    #[Property(sourceField: 'address.city')]
    private ?string $city = null;

    /**
     * Deep path: maps from address.postalCode in the source.
     */
    #[Property(sourceField: 'address.postalCode')]
    private ?string $postalCode = null;

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): void
    {
        $this->street = $street;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): void
    {
        $this->postalCode = $postalCode;
    }
}
