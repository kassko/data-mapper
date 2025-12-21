<?php

declare(strict_types=1);

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
