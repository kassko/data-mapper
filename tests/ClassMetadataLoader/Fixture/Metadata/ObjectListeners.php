<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Metadata;

use Kassko\DataMapper\Annotation as DM;

/**
 * Class ObjectListeners
 * 
 * @DM\ObjectListeners(
 *      classList={"classList#1"}
 * )
 */
class ObjectListeners
{
    /**
     * @return array
     */
    public static function loadInnerPhpMetadata()
    {
        return [
            'objectListeners'   => [
                'classList#1'
            ]
        ];
    }
}
