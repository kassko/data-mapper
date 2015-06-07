<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Metadata;

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

    /**
     * @return array
     */
    public static function loadInnerPhpMetadata()
    {
        return [
            'idComposite'   => ['firstField', 'secondField'],
            'fields'    => [
                'firstField'    => [
                    'name'  => 'firstFieldName'
                ],
                'secondField'   => [
                    'name'  => 'secondFieldName'
                ]
            ]
        ];
    }
}
