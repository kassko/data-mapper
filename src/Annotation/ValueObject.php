<?php

namespace Kassko\DataMapper\Annotation;

/**
* @Annotation
* @Target("PROPERTY")
*
* @deprecated
* @see Config
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
