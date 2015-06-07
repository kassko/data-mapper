<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Metadata;

use Kassko\DataMapper\Annotation as DM;

/**
 * Class CustomHydrator
 * 
 * @DM\CustomHydrator(
 *      class="CustomHydratorClassName",
 *      hydrateMethod="hydrateMethod",
 *      extractMethod="extractMethod"
 * )
 */
class CustomHydrator
{
    /**
     * @return array
     */
    public static function loadInnerPhpMetadata()
    {
        return [
            'object'    => [
                'customHydrator'    => [
                    'class' => 'CustomHydratorClassName',
                    'hydrateMethod' => 'hydrateMethod',
                    'extractMethod' => 'extractMethod'
                ]
            ]
        ];
    }
}
