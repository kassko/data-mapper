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

namespace Kassko\Sample\AttributeEnablement;

/**
 * Disabled data source for testing.
 * This should never be called since it's disabled.
 */
class DisabledDataSource
{
    public function getData(): array
    {
        throw new \RuntimeException('DisabledDataSource should never be called');
    }
}
