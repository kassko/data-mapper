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

namespace Kassko\Sample;

use Kassko\DataMapper\Attribute\MultiPropDataSource;
use Kassko\DataMapper\Attribute\DataSourceRef;
use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\ObjectExtension\LoadableInternalTrait;

#[DataSourcesStore([
    new MultiPropDataSource(id: 'personData', class: PersonDataSource::class, method: 'getData', args: ['#id'])
])]
class Person
{
    use LoadableInternalTrait;

    private int $id;

    #[DataSourceRef(id: 'personData')]
    private ?string $name = null;

    #[DataSourceRef(id: 'personData')]
    private ?string $email = null;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
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
}
