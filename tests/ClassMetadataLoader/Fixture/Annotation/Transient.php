<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Annotation;

use Kassko\DataMapper\Annotation as DM;

class Transient
{
    /**
     * @DM\Transient
     */
    protected $firstField;

    /**
     * @var string
     */
    protected $secondField;
}
