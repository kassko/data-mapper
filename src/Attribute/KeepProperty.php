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
 * Marks a property for inclusion in hydration.
 * This is an alias/alternative to Property when used only as an inclusion marker.
 * Can be used alongside Property attribute.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class KeepProperty
{
}
