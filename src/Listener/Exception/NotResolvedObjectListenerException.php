<?php

namespace Kassko\DataAccess\Listener\Exception;

/**
 * Exception thrown when a listener can't be create from class name.
 *
 * @author kko
 */
class NotResolvedObjectListenerException extends \RuntimeException
{
    public function __construct($className)
    {
        parent::__construct(sprintf("Can't create listener from class name [%s].", $className));
    }
}
