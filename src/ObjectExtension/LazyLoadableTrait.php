<?php

namespace Kassko\DataMapper\ObjectExtension;

use Kassko\DataMapper\Registry\Registry;

/**
 * Add Lazy loading feature to an object.
 *
 * @deprecated To be removed in the next significant release. Use LoadableTrait instead.
 * @see LoadableTrait
 *
 * @author kko
 */
trait LazyLoadableTrait
{
    use LoadableTrait;
}
