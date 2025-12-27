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
final class Getter
{
    public const TYPE_GETTER = 'getter';
    public const TYPE_ISSER = 'isser';
    public const TYPE_HASER = 'haser';

    public function __construct(
        public readonly ?string $name = null,  // Method name
        public readonly string $type = self::TYPE_GETTER,  // 'getter', 'isser', 'haser'
    ) {}
}
