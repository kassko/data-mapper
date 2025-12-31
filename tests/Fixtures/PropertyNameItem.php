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

use Kassko\DataMapper\Attribute\Property;

/**
 * Simple item fixture for PropertyNamePrecedence tests.
 * 
 * This class has a 'value' property. The Property.name or PropertyConfig.name
 * on the parent controls which raw data key maps to 'value'.
 */
class PropertyNameItem
{
    /**
     * The Property.name on the parent attribute controls which raw data key maps to this property.
     * - If parent has Property(name: 'property_value'), raw data['property_value'] maps here
     * - If parent has PropertyConfig(name: 'config_value') and no Property.name, raw data['config_value'] maps here
     */
    #[Property(name: 'property_value')]
    private ?string $value = null;

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): void
    {
        $this->value = $value;
    }
}
