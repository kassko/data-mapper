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
 * Data object for testing invalid configuration: both sourceField and MappingStrategy.
 * This should throw an exception.
 */
#[DM\MappingStrategy(preset: MappingStrategyPreset::FROM_UNDERSCORE_CASE)]
class PersonWithConflictingSourceField
{
    #[DM\Property(sourceField: 'explicit_first_name')]
    #[DM\MappingStrategy(preset: MappingStrategyPreset::FROM_DASH_CASE)]
    private ?string $firstName = null;

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }
}
