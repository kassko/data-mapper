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
 * INVALID: Multiple properties have DataSourceRef pointing to the SAME MultiPropDataSource id.
 * This should trigger a validation error.
 */
#[DataSourcesStore([
    new MultiPropDataSource(
        id: 'personData',
        class: PersonDataSource::class,
        method: 'getPersonData',
        args: ['#id']
    ),
])]
class DuplicateDataSourceRefOnProperties
{
    use LoadableTrait;

    private int $id = 1;

    // ERROR: Both properties reference the same MultiPropDataSource id
    #[DataSourceRef(id: 'personData')]
    private ?string $firstName = null;

    // ERROR: Duplicate reference to id 'personData'
    #[DataSourceRef(id: 'personData')]
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
        $this->loadProperty('lastName');
        return $this->lastName;
    }
}
