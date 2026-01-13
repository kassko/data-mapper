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

/**
 * Data source service that would typically be registered in a DI container.
 */
class UserDataSource
{
    public function getUser(int $id): array
    {
        return match($id) {
            1 => ['username' => 'admin', 'role' => 'ROLE_ADMIN'],
            2 => ['username' => 'editor', 'role' => 'ROLE_EDITOR'],
            default => ['username' => 'guest', 'role' => 'ROLE_USER'],
        };
    }
}
