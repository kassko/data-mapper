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
use Kassko\DataMapper\Attribute\DataSourceRef;

/**
 * Parent entity with data sources that should cascade to child classes.
 */
#[DataSourcesStore(items: [
    new DataSource(id: 'parentSource', class: ParentDataSource::class),
])]
class ParentWithDataSources
{
    #[Property(key: 'name')]
    protected string $name = '';

    public function getName(): string
    {
        return $this->name;
    }
}
