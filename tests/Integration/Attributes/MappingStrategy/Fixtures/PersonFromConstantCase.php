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

namespace Kassko\Sample\MappingStrategy;

use Kassko\DataMapper\Attribute as DM;
use Kassko\DataMapper\Enum\MappingStrategyPreset;

/**
 * Data object for testing CONSTANT_CASE source field mapping.
 * 
 * Source data uses CONSTANT_CASE: FIRST_NAME, LAST_NAME, BILLING_ADDRESS
 * Target properties are camelCase: firstName, lastName, billingAddress
 */
#[DM\MappingStrategy(preset: MappingStrategyPreset::FROM_CONSTANT_CASE)]
class PersonFromConstantCase
{
    private ?string $firstName = null;
    private ?string $lastName = null;
    private ?string $billingAddress = null;

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function getBillingAddress(): ?string
    {
        return $this->billingAddress;
    }
}
