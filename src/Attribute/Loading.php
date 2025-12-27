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

namespace Kassko\DataMapper\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class Loading
{
    public const TYPE_LAZY = 'lazy';
    public const TYPE_EAGER = 'eager';

    public function __construct(
        public readonly string $type = self::TYPE_LAZY,  // 'lazy' or 'eager'
        public readonly ?int $depth = null,              // Max recursion depth
    ) {}
}
