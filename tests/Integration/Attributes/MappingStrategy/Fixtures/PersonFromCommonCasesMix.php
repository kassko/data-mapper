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
 * Data object for testing common cases mix source field mapping (default).
 * 
 * Source data uses mixed cases: first_name, last-name, billingAddress
 * Target properties are camelCase: firstName, lastName, billingAddress
 */
class PersonFromCommonCasesMix
{
    private ?string $firstName = null;
    private ?string $lastName = null;
    private ?string $billingAddress = null;
    private ?string $homeDeliveryAddress = null;

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

    public function getHomeDeliveryAddress(): ?string
    {
        return $this->homeDeliveryAddress;
    }
}
