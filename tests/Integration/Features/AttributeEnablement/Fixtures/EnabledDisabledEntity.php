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
use Kassko\DataMapper\Attribute\Property;
use Kassko\DataMapper\Attribute\DataSourceRef;

/**
 * Test entity for enabled/disabled attribute functionality.
 */
#[DataSourcesStore(items: [
    new DataSource(id: 'enabledSource', class: EnabledDataSource::class),
    new DataSource(id: 'disabledSource', class: DisabledDataSource::class, enabled: false),
])]
class EnabledDisabledEntity
{
    #[Property(sourceField: 'name')]
    private string $name = '';

    #[Property(sourceField: 'description', enabled: false)]
    private string $disabledProperty = '';

    #[Property(sourceField: 'extra')]
    #[DataSourceRef(id: 'enabledSource')]
    private string $fromEnabledSource = '';

    #[Property(sourceField: 'extra')]
    #[DataSourceRef(id: 'disabledSource')]
    private string $fromDisabledSource = '';

    public function getName(): string
    {
        return $this->name;
    }

    public function getDisabledProperty(): string
    {
        return $this->disabledProperty;
    }

    public function getFromEnabledSource(): string
    {
        return $this->fromEnabledSource;
    }

    public function getFromDisabledSource(): string
    {
        return $this->fromDisabledSource;
    }
}
