<?php

namespace Kassko\DataMapper\Annotation;

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