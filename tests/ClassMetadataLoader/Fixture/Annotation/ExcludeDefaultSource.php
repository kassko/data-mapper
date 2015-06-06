<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Annotation;

use Kassko\DataMapper\Annotation as DM;

class ExcludeDefaultSource
{
    /**
     * @DM\ExcludeDefaultSource
     */
    protected $excludeDefaultSourceField;
}