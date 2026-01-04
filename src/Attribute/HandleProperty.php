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
 * Controls whether a specific property should be handled/hydrated.
 * 
 * When value is true: Property is explicitly included for hydration.
 * When value is false: Property is explicitly excluded from hydration.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class HandleProperty
{
    /**
     * @param bool $value Whether to handle/hydrate this property
     * @param string|null $when Optional expression to conditionally apply this setting.
     *                          If null (default), the value is always applied.
     *                          If expression evaluates to true, the value is applied.
     *                          If expression evaluates to false, this attribute is ignored.
     * @param bool $cascade Whether this attribute cascades to child classes
     * @param bool $enabled Whether this attribute is active (disabled attributes are ignored)
     */
    public function __construct(
        public readonly bool $value = true,
        public readonly ?string $when = null,
        public readonly bool $cascade = true,
        public readonly bool $enabled = true,
    ) {}
}
