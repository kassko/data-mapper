<?php

namespace Kassko\DataAccess\Result\Exception;

/**
 * Exception thrown when an index does not exist in an array of result.
 *
 * @author kko
 */
class NotFoundIndexException extends \Exception
{
    public function __construct($objectClass, $propertyName)
    {
        parent::__construct(
            sprintf('Property "%s" does not exist for object "%s".', $propertyName, $objectClass)
        );
    }
}
