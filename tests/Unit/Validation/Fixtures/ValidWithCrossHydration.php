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
use Kassko\DataMapper\Attribute\Property;
use Kassko\DataMapper\Attribute\DataSourceRef;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

/**
 * Valid usage: Cross-hydration pattern.
 * Only ONE property has DataSourceRef, others get hydrated from matching keys.
 */
#[DataSourcesStore([
    new MultiPropDataSource(
        id: 'personData',
        class: PersonDataSource::class,
        method: 'getPersonData',
        args: ['#id']
    ),
])]
class ValidWithCrossHydration
{
    use LoadableTrait;

    private int $id = 1;

    // Only ONE property explicitly references the MultiPropDataSource
    #[DataSourceRef(id: 'personData')]
    #[Property(sourceField: 'first_name')]
    private ?string $firstName = null;

    // Cross-hydrated from 'personData' result using sourceField mapping
    #[Property(sourceField: 'last_name')]
    private ?string $lastName = null;

    // Cross-hydrated from 'personData' result using property name as key
    private ?string $email = null;

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

    public function getEmail(): ?string
    {
        return $this->email;
    }
}
