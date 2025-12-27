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

/**
 * Alias for SinglePropDataSource.
 * 
 * Use this when you only need single-property hydration in your project.
 * Use SinglePropDataSource when you mix single and multi-property hydration
 * for clearer code (SinglePropDataSource + MultiPropDataSource).
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class DataSource
{
    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $class = null,
        public readonly string $method = '',
        public readonly array $args = [],
        public readonly int $priority = 0,
    ) {}
}
