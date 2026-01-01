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
 * Base class with DataSourcesStore for testing cascading.
 */
#[DataSourcesStore([
    new SinglePropDataSource(id: 'parentSource', class: ParentDataSource::class, method: 'getData'),
    new SinglePropDataSource(id: 'sharedSource', class: ParentDataSource::class, method: 'getShared'),
])]
abstract class BaseEntity
{
    private ?int $id = null;
    private ?string $parentValue = null;
    
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function setId(?int $id): void
    {
        $this->id = $id;
    }
    
    public function getParentValue(): ?string
    {
        return $this->parentValue;
    }
}
