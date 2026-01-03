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

namespace Kassko\Sample\PropertyInstantiatingHook;

/**
 * Data source for TrackedEntity.
 */
class TrackedEntityDataSource
{
    public function getData(int $id): ?string
    {
        return match ($id) {
            1 => 'Entity One',
            2 => 'Entity Two',
            default => 'Unknown Entity',
        };
    }
}
