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

namespace Kassko\Sample\MultiPropUniqueness;

use Kassko\DataMapper\Attribute\MultiPropDataSource;
use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\Attribute\DataSourceRef;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

/**
 * Valid usage: Single property references a MultiPropDataSource.
 * Other properties are cross-hydrated automatically.
 */
#[DataSourcesStore([
    new MultiPropDataSource(
        id: 'personData',
        class: PersonDataSource::class,
        method: 'getPersonData',
        args: ['#id']
    ),
])]
class ValidSingleSource
{
    use LoadableTrait;

    private int $id = 1;

    // Only ONE property explicitly references the MultiPropDataSource
    #[DataSourceRef(id: 'personData')]
    private ?string $firstName = null;

    // Cross-hydrated from 'personData' result - no DataSourceRef needed
    private ?string $lastName = null;

    public function __construct(int $id = 1)
    {
        $this->id = $id;
    }

    public function getFirstName(): ?string
    {
        $this->loadProperty('firstName');
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }
}
