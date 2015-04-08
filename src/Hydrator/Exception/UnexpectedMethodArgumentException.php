<?php

namespace Kassko\DataMapper\Hydrator\Exception;

use Exception;

/**
 * Create exceptions about expression reading.
 *
 * @author kko
 */
class UnexpectedMethodArgumentException extends Exception
{
    /**
     * @param expression The expression which cannot be ridden.
     */
    public function __construct($expression)
    {
        parent::__construct(sprintf('The expression "%s" cannot be evaluated.', $expression), 0, null);
    }   
}
