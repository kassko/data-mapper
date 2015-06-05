<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Annotation;

use Kassko\DataMapper\Annotation as DM;

class Exclude
{
    /**
     * @DM\Exclude
     */
    protected $excludedField;

    /**
     * @DM\Field
     */
    protected $field;
}
