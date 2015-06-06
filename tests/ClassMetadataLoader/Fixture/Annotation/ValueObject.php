<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Annotation;

use Kassko\DataMapper\Annotation as DM;

class ValueObject
{
    /**
     * @DM\ValueObject(
     *      class="\ValueObjectClass",
     *      mappingResourceName="valueObjectResourceName",
     *      mappingResourcePath="valueObjectResourcePath",
     *      mappingResourceType="valueObjectResourceType"
     * )
     */
    protected $firstField;
}
