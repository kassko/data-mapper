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

namespace Kassko\Sample\LoadingDepthExpand;

/**
 * Data source providing nested team data for testing depth + expand collaboration.
 */
class TeamDataSource
{
    /**
     * Returns nested team data with 2 levels of depth:
     * - Team (level 0)
     *   - Employee manager (level 1)
     *   - Employee assistant (level 1)
     *   - Employee[] members (level 1)
     */
    public static function getData(): array
    {
        return [
            'name' => 'Engineering Team',
            'code' => 'ENG-001',
            'manager' => [
                'name' => 'Alice Manager',
                'role' => 'Engineering Manager',
                'email' => 'alice@example.com',
            ],
            'assistant' => [
                'name' => 'Bob Assistant',
                'role' => 'Team Assistant',
                'email' => 'bob@example.com',
            ],
            'members' => [
                [
                    'name' => 'Charlie Developer',
                    'role' => 'Senior Developer',
                    'email' => 'charlie@example.com',
                ],
                [
                    'name' => 'Diana Developer',
                    'role' => 'Developer',
                    'email' => 'diana@example.com',
                ],
            ],
        ];
    }
}
