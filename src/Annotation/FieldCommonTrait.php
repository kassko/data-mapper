<?php

namespace Kassko\DataMapper\Annotation;

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
    public $readDateConverter;

    /**
    * @var string
    */
    public $writeDateConverter;

    /**
    * @var string
    */
    public $fieldMappingExtensionClass;
}