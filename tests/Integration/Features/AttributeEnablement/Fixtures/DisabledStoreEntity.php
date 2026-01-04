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

namespace Kassko\Sample\AttributeEnablement;

use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\Attribute\DataSource;

/**
 * Test entity with completely disabled DataSourcesStore.
 */
#[DataSourcesStore(items: [
    new DataSource(id: 'testSource', class: EnabledDataSource::class),
], enabled: false)]
class DisabledStoreEntity
{
    private string $name = '';

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
