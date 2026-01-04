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

#[Attribute(Attribute::TARGET_PROPERTY)]
final class Setter
{
    public const TYPE_SETTER = 'setter';
    public const TYPE_ADDER = 'adder';

    public function __construct(
        public readonly ?string $name = null,  // Method name
        public readonly string $type = self::TYPE_SETTER,  // 'setter' or 'adder'
        public readonly bool $cascade = true,  // Whether this attribute cascades to child classes
        public readonly bool $enabled = true,  // Whether this attribute is active (disabled attributes are ignored)
    ) {}
}
