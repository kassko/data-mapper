<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Metadata;

use Kassko\DataMapper\Annotation as DM;

class ExcludeDefaultSource
{
    /**
     * @DM\ExcludeDefaultSource
     */
    protected $excludeDefaultSourceField;

    /**
     * @return array
     */
    public static function loadInnerPhpMetadata()
    {
        return [
        ];
    }
}