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

namespace Kassko\Sample\MultiPropConflict;

use Kassko\DataMapper\Attribute\MultiPropDataSource;
use Kassko\DataMapper\Attribute\SinglePropDataSource;
use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\Attribute\DataSourceRef;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

/**
 * Test case: No priority defined on either source.
 * Expected: Property's own source wins.
 * 
 * - firstName is explicitly referenced by personData
 * - lastName is cross-hydrated from personData
 * - avatar has its own SinglePropDataSource AND could be cross-hydrated from personData
 */
#[DataSourcesStore([
    new MultiPropDataSource(
        id: 'personData',
        class: PersonDataSource::class,
        method: 'getPersonData',
        args: ['#id']
        // No priority defined (defaults to 0)
    ),
])]
class PersonWithConflictingSourcesNoPriority
{
    use LoadableTrait;

    private int $id;

    // Explicitly references personData
    #[DataSourceRef(id: 'personData')]
    private ?string $firstName = null;

    // Cross-hydrated from personData (no explicit reference)
    private ?string $lastName = null;

    // Has its own DataSource AND could be cross-hydrated from personData
    // No priority defined - property source should win
    #[SinglePropDataSource(
        class: AvatarService::class,
        method: 'getAvatar',
        args: ['#id']
        // No priority defined (defaults to 0)
    )]
    private ?string $avatar = null;

    public function __construct(int $id)
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
        // This is cross-hydrated when firstName is loaded
        return $this->lastName;
    }

    public function getAvatar(): ?string
    {
        $this->loadProperty('avatar');
        return $this->avatar;
    }
}
