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

/**
 * Data source for User entity.
 */
class UserDataSource
{
    private static array $users = [
        1 => ['username' => 'admin', 'email' => 'admin@example.com', 'role' => 'ROLE_ADMIN'],
        2 => ['username' => 'editor', 'email' => 'editor@example.com', 'role' => 'ROLE_EDITOR'],
        3 => ['username' => 'guest', 'email' => 'guest@example.com', 'role' => 'ROLE_USER'],
    ];

    public function getUsername(int $id): ?string
    {
        return self::$users[$id]['username'] ?? null;
    }

    public function getEmail(int $id): ?string
    {
        return self::$users[$id]['email'] ?? null;
    }

    public function getRole(int $id): ?string
    {
        return self::$users[$id]['role'] ?? null;
    }
}
