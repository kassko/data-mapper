<?php

namespace Kassko\DataMapper\Result\Exception;

/**
* Exception thrown when no result was found.
*
* @author kko
*/
class NoResultException extends \Exception
{
    public function __construct($objectClass)
    {
        parent::__construct(sprintf('No result found. Object is "%s".', $objectClass));
    }
}
