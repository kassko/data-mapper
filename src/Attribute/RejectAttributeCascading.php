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
 * Rejects attribute cascading from parent classes and traits.
 * 
 * When a class declares this attribute:
 * - It does NOT inherit attributes from parent classes and included traits
 * - It still cascades its own attributes to child classes by default
 * - It acts as a "reset" point in the inheritance chain
 * 
 * This allows a class to start fresh without inheriting DataSourcesStore,
 * PropertyConfigStore, and other cascaded attributes from ancestors.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class RejectAttributeCascading
{
    /**
     * @param bool $enabled Whether this attribute is active (disabled attributes are ignored)
     */
    public function __construct(
        public readonly bool $enabled = true,
    ) {}
}
