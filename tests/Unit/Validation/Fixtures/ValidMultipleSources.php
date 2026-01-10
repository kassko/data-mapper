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
 * Valid usage: Multiple MultiPropDataSources with DIFFERENT ids.
 * Each id is referenced by only ONE property.
 */
#[DataSourcesStore([
    new MultiPropDataSource(
        id: 'basicInfo',
        class: PersonDataSource::class,
        method: 'getBasicInfo',
        args: ['#id']
    ),
    new MultiPropDataSource(
        id: 'contactInfo',
        class: PersonDataSource::class,
        method: 'getContactInfo',
        args: ['#id']
    ),
])]
class ValidMultipleSources
{
    use LoadableTrait;

    private int $id = 1;

    // One reference per MultiPropDataSource id
    #[DataSourceRef(id: 'basicInfo')]
    private ?string $firstName = null;

    // Cross-hydrated from 'basicInfo'
    private ?string $lastName = null;

    // One reference per MultiPropDataSource id (different id)
    #[DataSourceRef(id: 'contactInfo')]
    private ?string $email = null;

    // Cross-hydrated from 'contactInfo'
    private ?string $phone = null;

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
        $this->loadProperty('email');
        return $this->email;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }
}
