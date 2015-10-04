<?php

namespace Kassko\DataMapper\Annotation;

/**
* @Annotation
* @Target({"PROPERTY"})
*
* @author kko
*/
final class Variables
{
	/**
    * @var array
    */
    public $variables = [];

    public function __construct(array $variables)
    {
		$this->variables = $variables;
    }
}
