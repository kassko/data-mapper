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
 * Data object for testing string preset values.
 */
#[DM\MappingStrategy(preset: 'from_underscore_case')]
class PersonWithStringPreset
{
    private ?string $firstName = null;
    private ?string $lastName = null;

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }
}
