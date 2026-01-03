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

use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\Attribute\SinglePropDataSource;

/**
 * Trait with DataSourcesStore for testing cascading.
 */
#[DataSourcesStore([
    new SinglePropDataSource(id: 'traitSource', class: TraitDataSource::class, method: 'getData'),
    new SinglePropDataSource(id: 'sharedSource', class: TraitDataSource::class, method: 'getShared'),
])]
trait DataSourceTrait
{
    private ?string $traitValue = null;
    
    public function getTraitValue(): ?string
    {
        return $this->traitValue;
    }
}
