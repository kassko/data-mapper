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

namespace Kassko\Sample\LoadingDepth;

/**
 * Data source providing nested department data for testing depth limits.
 */
class DepartmentDataSource
{
    /**
     * Returns nested department data with 3 levels of depth:
     * - Department (level 0)
     *   - Team (level 1)
     *     - Employee leader (level 2)
     *     - Employee[] members (level 2)
     */
    public static function getData(): array
    {
        return [
            'name' => 'Engineering',
            'code' => 'ENG-001',
            'mainTeam' => [
                'name' => 'Backend Team',
                'leader' => [
                    'name' => 'Alice Johnson',
                    'role' => 'Tech Lead',
                ],
                'members' => [
                    [
                        'name' => 'Bob Smith',
                        'role' => 'Senior Developer',
                    ],
                    [
                        'name' => 'Carol White',
                        'role' => 'Developer',
                    ],
                ],
            ],
        ];
    }
}
