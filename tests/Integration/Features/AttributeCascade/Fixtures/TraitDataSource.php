<?php

declare(strict_types=1);

/*
 * This file is part of DataMapper.
 *
 * Copyright 2025 kassko 
 *
 * For the full copyright and license information,
 * please view the LICENSE and NOTICE files that were distributed with this source code.
 */

namespace Kassko\Sample\Cascade;

/**
 * Data source class for trait tests.
 */
class TraitDataSource
{
    public function getData(): string
    {
        return 'from-trait';
    }

    public function getShared(): string
    {
        return 'from-trait-shared';
    }
}
