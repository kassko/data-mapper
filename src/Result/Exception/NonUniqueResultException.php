<?php

namespace Kassko\DataAccess\Result\Exception;

/**
* Exception thrown when one result is expected but a collection results is obtained.
*
* @author kko
*/
class NonUniqueResultException extends \Exception
{
	public function __construct($objectClass)
	{
		parent::__construct(
            sprintf(
                'Only one result was expected but several results obtained for object "%s".',
                $objectClass
            )
        );
	}
}
