<?php

namespace Kassko\DataAccess\Annotation;

/**
* @Annotation
* @Target("CLASS")
*
* @author kko
*/
final class ObjectListeners
{
	/**
	* @var array
	*/
    public $classList = [];
}