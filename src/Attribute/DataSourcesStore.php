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

namespace Kassko\DataMapper\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class DataSourcesStore
{
    /**
     * @param array<SinglePropDataSource|DataSource|MultiPropDataSource> $sources
     */
    public function __construct(
        public readonly array $sources = [],
    ) {}
}
