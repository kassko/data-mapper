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

namespace Kassko\Sample\Portable;

use Kassko\DataMapper\Attribute\MultiPropDataSource;
use Kassko\DataMapper\Attribute\DataSourceRef;
use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

/**
 * Entity that uses a service reference (@service.id pattern) for its data source.
 * 
 * This demonstrates how DataMapper can work with DI containers.
 */
#[DataSourcesStore([
    new MultiPropDataSource(id: 'userData', class: '@user.data_source', method: 'getUser', args: ['#id'])
])]
class User
{
    use LoadableTrait;

    private int $id;

    #[DataSourceRef(id: 'userData')]
    private ?string $username = null;

    #[DataSourceRef(id: 'userData')]
    private ?string $role = null;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        $this->loadProperty('username');
        return $this->username;
    }

    public function getRole(): ?string
    {
        $this->loadProperty('role');
        return $this->role;
    }
}
