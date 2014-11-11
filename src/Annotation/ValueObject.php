<?php

namespace Kassko\DataAccess\Annotation;

/**
* @Annotation
* @Target("PROPERTY")
*
* @author kko
*/
final class ValueObject
{
	/**
	* @var string
	*/
    public $class;

    /**
    * @var string
    */
    public $mappingResourceName;

    /**
	* @var string
	*/
    public $mappingResourcePath;

    /**
    * @var string
    */
    public $mappingResourceType;
}
