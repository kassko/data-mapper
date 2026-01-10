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
 * INVALID: Mix of violations - both duplicate DataSourceRef references.
 * This should trigger multiple validation errors.
 */
#[DataSourcesStore([
    new MultiPropDataSource(
        id: 'userData',
        class: PersonDataSource::class,
        method: 'getUserData',
        args: ['#id']
    ),
])]
class MixedDuplicateViolation
{
    use LoadableTrait;

    private int $id = 1;

    // ERROR: Multiple properties reference the same MultiPropDataSource id 'userData'
    #[DataSourceRef(id: 'userData')]
    private ?string $firstName = null;

    // ERROR: Duplicate reference to id 'userData'
    #[DataSourceRef(id: 'userData')]
    private ?string $lastName = null;

    // ERROR: Another duplicate reference to id 'userData'
    #[DataSourceRef(id: 'userData')]
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
        $this->loadProperty('lastName');
        return $this->lastName;
    }

    public function getEmail(): ?string
    {
        $this->loadProperty('email');
        return $this->email;
    }
}
