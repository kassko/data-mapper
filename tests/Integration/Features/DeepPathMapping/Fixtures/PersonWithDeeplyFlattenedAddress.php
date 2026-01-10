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
 * Domain object with 3-level deep path mapping.
 * 
 * Maps from PersonWithDeepAddressDto:
 * - firstName -> firstName
 * - address.street.name -> streetName
 * - address.street.number -> streetNumber
 * - address.city -> city
 */
class PersonWithDeeplyFlattenedAddress
{
    #[Property(sourceField: 'firstName')]
    private ?string $firstName = null;

    #[Property(sourceField: 'lastName')]
    private ?string $lastName = null;

    /**
     * 3-level deep path: maps from address.street.name
     */
    #[Property(sourceField: 'address.street.name')]
    private ?string $streetName = null;

    /**
     * 3-level deep path: maps from address.street.number
     */
    #[Property(sourceField: 'address.street.number')]
    private ?int $streetNumber = null;

    /**
     * 2-level deep path: maps from address.city
     */
    #[Property(sourceField: 'address.city')]
    private ?string $city = null;

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

    public function getStreetName(): ?string
    {
        return $this->streetName;
    }

    public function setStreetName(?string $streetName): void
    {
        $this->streetName = $streetName;
    }

    public function getStreetNumber(): ?int
    {
        return $this->streetNumber;
    }

    public function setStreetNumber(?int $streetNumber): void
    {
        $this->streetNumber = $streetNumber;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): void
    {
        $this->city = $city;
    }
}
