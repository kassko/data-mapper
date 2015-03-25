<?php

namespace Kassko\DataMapper\Annotation;

/**
* @Annotation
* @Target({"PROPERTY"})
*
* @author kko
*/
final class RefSource
{
	/**
     * Id of a source (data source, provider)
     * @var string
     */
    public $ref;
}