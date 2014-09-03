<?php

namespace Kassko\DataAccess\Annotation;

/**
* @Annotation
* @Target("CLASS")
*
* @author kko
*/
final class EntityListeners
{
	/**
	* @var array
	*/
    public $classList = [];
}