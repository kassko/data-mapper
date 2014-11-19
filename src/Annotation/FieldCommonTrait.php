<?php

namespace Kassko\DataAccess\Annotation;

/**
* Property annotations to be used in Column annotation or Entity annotation.
*
* A property used in Entity annotation works for all properties.
* A property used in Column annotation only works for the annotated property.
* If a property used in both, the Column annotation is priority.
*
* @author kko
*/
trait FieldCommonTrait
{
	/**
    * @var string
    */
    public $readDateFormat;

    /**
    * @var string
    */
    public $writeDateFormat;

    /**
	* @var string
	*/
    public $metadataExtensionClass;
}