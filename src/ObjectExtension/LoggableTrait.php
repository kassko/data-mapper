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

        if (null === $logger) {

            $registry = Registry::getInstance();
            if (isset($registry[Registry::KEY_LOGGER])) {
                $logger = $registry[Registry::KEY_LOGGER]->getInstance(get_called_class());
            } else {
                $logger = false;
            }
        }

        return $logger; 
    }
}
