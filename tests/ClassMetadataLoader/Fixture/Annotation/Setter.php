<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Annotation;

use Kassko\DataMapper\Annotation as DM;

class Setter
{
    /**
     * @DM\Setter(
     *      prefix="setterPrefix",
     *      name="setterName"
     * )
     */
    protected $firstField;
}
