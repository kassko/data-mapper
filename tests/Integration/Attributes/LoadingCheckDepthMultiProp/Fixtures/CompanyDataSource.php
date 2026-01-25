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

namespace Kassko\Sample\LoadingDepthMultiProp;

/**
 * Data source providing company data with nested team.
 */
class CompanyDataSource
{
    public static function getData(): array
    {
        return [
            'name' => 'Acme Corporation',
            'code' => 'ACME-001',
            'team' => [
                'name' => 'Engineering Team',
                'leader' => [
                    'name' => 'Alice Manager',
                    'role' => 'Tech Lead',
                ],
            ],
        ];
    }
}
