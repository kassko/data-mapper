<?php

namespace Kassko\DataMapper\Annotation;

/**
* @Annotation
* @Target("PROPERTY")
*
* @author kko
*/
final class Config
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
