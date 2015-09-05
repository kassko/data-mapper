<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Metadata;

use Kassko\DataMapper\Annotation as DM;

/**
 * Class PreHydrate
 * 
 * @DM\PreHydrate(
 *      class="CustomHydratorClassName",
 *      method="preHydrateMethodName"
 * )
 */
class PreHydrate
{
    /**
     * @return array
     */
    public static function loadInnerPhpMetadata()
    {
        return [
            'interceptors'  => [
                'preHydrate'    => 'preHydrateMethodName'
            ]
        ];
    }

    /**
     * @return string
     */
    public static function loadInnerYamlMetadata()
    {
        return <<<EOF
interceptors:
  preHydrate: preHydrateMethodName
EOF;
    }
}
