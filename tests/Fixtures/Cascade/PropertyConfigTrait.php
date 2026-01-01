<?php

declare(strict_types=1);

/*
 * This file is part of DataMapper.
 *
 * Copyright 2025 kassko 
 *
 * For the full copyright and license information,
 * please view the LICENSE and NOTICE files that were distributed with this source code.
 */

namespace Kassko\Sample\Cascade;

use Kassko\DataMapper\Attribute\PropertyConfig;
use Kassko\DataMapper\Attribute\PropertyConfigStore;

/**
 * Trait with PropertyConfigStore for testing cascading.
 */
#[PropertyConfigStore([
    new PropertyConfig(id: 'traitConfig', class: TraitProduct::class),
    new PropertyConfig(id: 'sharedConfig', class: TraitProduct::class),
])]
trait PropertyConfigTrait
{
}
