<?php

namespace Kassko\DataAccess\ObjectExtension;

use Kassko\DataAccess\Registry\Registry;

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
