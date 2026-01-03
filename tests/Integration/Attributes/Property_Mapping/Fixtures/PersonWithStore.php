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

namespace Kassko\Sample\PropertyMapping;

use Kassko\DataMapper\Attribute\MultiPropDataSource;
use Kassko\DataMapper\Attribute\DataSourceRef;
use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\Attribute\Property;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

#[DataSourcesStore([
    new MultiPropDataSource(
        id: 'personSource',
        class: PersonDataSource::class,
        method: 'getData',
        args: ['#id']
    )
])]
class PersonWithStore
{
    use LoadableTrait;

    private int $id;

    #[DataSourceRef(id: 'personSource')]
    #[Property(name: 'first_name')]
    private ?string $firstName = null;

    #[DataSourceRef(id: 'personSource')]
    private ?string $name = null;

    #[DataSourceRef(id: 'personSource')]
    private ?string $email = null;

    // This property should NOT be hydrated (no DataSourceRef)
    private ?int $age = null;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        $this->loadProperty('firstName');
        return $this->firstName;
    }

    public function getName(): ?string
    {
        $this->loadProperty('name');
        return $this->name;
    }

    public function getEmail(): ?string
    {
        $this->loadProperty('email');
        return $this->email;
    }

    public function getAge(): ?int
    {
        $this->loadProperty('age');
        return $this->age;
    }
}
