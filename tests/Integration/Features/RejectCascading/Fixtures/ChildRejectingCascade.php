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

namespace Kassko\Sample\RejectCascading;

use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\Attribute\DataSource;
use Kassko\DataMapper\Attribute\Property;
use Kassko\DataMapper\Attribute\RejectAttributeCascading;

/**
 * Child entity that rejects cascading from parent.
 * This means it should NOT inherit parentSource from ParentWithDataSources.
 */
#[RejectAttributeCascading]
#[DataSourcesStore(items: [
    new DataSource(id: 'childSource', class: ChildDataSource::class),
])]
class ChildRejectingCascade extends ParentWithDataSources
{
    #[Property(sourceField: 'email')]
    private string $email = '';

    public function getEmail(): string
    {
        return $this->email;
    }
}
