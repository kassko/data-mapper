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
 * Data object for testing property-level override of class-level mapping strategy.
 * 
 * Class uses underscore_case, but lastName is overridden to dash-case.
 */
#[DM\MappingStrategy(preset: MappingStrategyPreset::FROM_UNDERSCORE_CASE)]
class PersonWithPropertyOverride
{
    private ?string $firstName = null;
    
    #[DM\MappingStrategy(preset: MappingStrategyPreset::FROM_DASH_CASE)]
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
