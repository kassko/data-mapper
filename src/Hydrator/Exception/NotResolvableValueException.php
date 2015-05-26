<?php

namespace Kassko\DataMapper\Hydrator\Exception;

use Exception;

/**
 * Create exceptions about value resolving.
 *
 * @author kko
 */
class NotResolvableValueException extends Exception
{
    /**
     * @param value The value that cannot be resolved.
     */
    public function __construct($value)
    {
        parent::__construct(sprintf('The value "%s" cannot be resolved.', $value), 0, null);
    }   
}
