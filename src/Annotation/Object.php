<?php

namespace Kassko\DataAccess\Annotation;

/**
* @Annotation
* @Target("CLASS")
*
* @author kko
*/
final class Object
{
	use FieldCommonTrait;

    /**
	* @var string
	*/
    public $repositoryClass;

    /**
	* @var boolean
	*/
    public $propertyAccessStrategyEnabled = false;

    /**
	* @var boolean
	*/
    public $readOnly = false;
}