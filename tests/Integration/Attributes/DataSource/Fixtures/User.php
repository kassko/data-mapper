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

namespace Kassko\Sample\DataSource;

use Kassko\DataMapper\Attribute\DataSource;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

/**
 * Basic user entity using DataSource for lazy loading.
 * DataSource is an alias for SinglePropDataSource.
 */
class User
{
    use LoadableTrait;

    private int $id;

    #[DataSource(class: UserDataSource::class, method: 'getUsername', args: ['#id'])]
    private ?string $username = null;

    #[DataSource(class: UserDataSource::class, method: 'getEmail', args: ['#id'])]
    private ?string $email = null;

    #[DataSource(class: UserDataSource::class, method: 'getRole', args: ['#id'])]
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

    public function getEmail(): ?string
    {
        $this->loadProperty('email');
        return $this->email;
    }

    public function getRole(): ?string
    {
        $this->loadProperty('role');
        return $this->role;
    }
}
