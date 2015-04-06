<?php

namespace Kassko\DataMapper\Expression\Exception;

use Exception;

/**
 * Create exceptions about expression reading.
 *
 * @author kko
 */
class UnexpectedExpressionException extends Exception
{
    /**
     * @param expression The expression which cannot be ridden.
     */
    public function __construct($expression)
    {
        parent::__construct('The expression "%s" cannot be evaluated.', $expression);
    }   
}
