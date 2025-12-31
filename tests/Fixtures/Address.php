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

namespace Kassko\Sample;

/**
 * Simple Address fixture for testing CustomHydrator.
 */
class Address
{
    public function __construct(
        public readonly string $street,
        public readonly string $city,
        public readonly string $zipCode,
        public readonly string $country = 'France'
    ) {}

    public function __toString(): string
    {
        return sprintf('%s, %s %s, %s', $this->street, $this->zipCode, $this->city, $this->country);
    }
}
