<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Annotation;

use Kassko\DataMapper\Annotation as DM;

class Version
{
    /**
     * @DM\Version
     */
    protected $firstField;
}
