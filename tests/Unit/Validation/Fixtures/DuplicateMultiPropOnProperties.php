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
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

/**
 * INVALID: Multiple properties declare MultiPropDataSource with the SAME id.
 * This should trigger a validation error.
 */
class DuplicateMultiPropOnProperties
{
    use LoadableTrait;

    private int $id = 1;

    // ERROR: Both properties declare MultiPropDataSource with same id 'personData'
    #[MultiPropDataSource(
        id: 'personData',
        class: PersonDataSource::class,
        method: 'getFirstName',
        args: ['#id']
    )]
    private ?string $firstName = null;

    // ERROR: Duplicate id 'personData'
    #[MultiPropDataSource(
        id: 'personData',
        class: PersonDataSource::class,
        method: 'getLastName',
        args: ['#id']
    )]
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
