<?php

namespace Kassko\DataAccess\Result\Exception;

/**
* Exception thrown when an index of result set is reused.
*
* @author kko
*/
class DuplicatedIndexException extends \Exception
{
	public function __construct($index, $objectClass)
	{
		parent::__construct(
            sprintf(
                'Index "%s" of result set is already used. Object is "%s".',
                $index,
                $objectClass
            )
        );
	}
}
