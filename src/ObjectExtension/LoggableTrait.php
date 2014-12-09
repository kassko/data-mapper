<?php

namespace Kassko\DataMapper\ObjectExtension;

use Kassko\DataMapper\Registry\Registry;

/**
 * Add log feature to an entity.
 *
 * @author kko
 */
trait LoggableTrait
{
    public function getLogger()
    {
        static $logger;
        return $logger = $logger ?: Registry::getInstance()[Registry::KEY_LOGGER];
    }
}
