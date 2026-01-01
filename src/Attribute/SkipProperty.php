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
final class SkipProperty
{
    /**
     * @param string|null $when Optional expression to conditionally skip the property.
     *                          If null (default), property is always skipped.
     *                          If expression evaluates to true, property is skipped.
     *                          If expression evaluates to false, property is not skipped.
     * @param bool $cascade Whether this attribute cascades to child classes
     */
    public function __construct(
        public readonly ?string $when = null,
        public readonly bool $cascade = true,
    ) {}
}
