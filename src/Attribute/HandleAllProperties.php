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
 * Controls default handling behavior for all properties in a class.
 * 
 * When value is true (default): All properties are handled/hydrated by default.
 * When value is false: Only properties marked with HandleProperty(value: true) or Property are handled.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class HandleAllProperties
{
    /**
     * @param bool $value Whether to handle all properties by default (true) or skip all by default (false)
     * @param bool $cascade Whether this attribute cascades to child classes
     * @param bool $enabled Whether this attribute is active (disabled attributes are ignored)
     */
    public function __construct(
        public readonly bool $value = true,
        public readonly bool $cascade = true,
        public readonly bool $enabled = true,
    ) {}
}
