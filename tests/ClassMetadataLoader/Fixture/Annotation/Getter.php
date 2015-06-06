<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Annotation;

use Kassko\DataMapper\Annotation as DM;

class Getter
{
    /**
     * @DM\Getter(
     *      prefix="getterPrefix",
     *      name="getterName"
     * )
     */
    protected $firstField;
}
