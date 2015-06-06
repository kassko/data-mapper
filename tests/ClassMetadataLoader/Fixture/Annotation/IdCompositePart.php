<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Annotation;

use Kassko\DataMapper\Annotation as DM;

class IdCompositePart
{
    /**
     * @DM\IdCompositePart
     */
    protected $firstField;

    /**
     * @DM\IdCompositePart
     */
    protected $secondField;
}
