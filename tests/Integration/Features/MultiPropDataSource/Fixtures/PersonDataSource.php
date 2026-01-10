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

namespace Kassko\Sample\MultiPropConflict;

/**
 * Data source that returns person data including an 'avatar' key
 * that can potentially cross-hydrate other properties.
 */
class PersonDataSource
{
    public function getPersonData(int $id): array
    {
        return [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john.doe@example.com',
            'avatar' => 'avatar-from-person-source.png',  // This could cross-hydrate avatar property
        ];
    }
}
