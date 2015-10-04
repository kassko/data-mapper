<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Metadata;

use Kassko\DataMapper\Annotation as DM;

/**
 * Class PreHydrate
 * 
 * @DM\PreHydrate(
 *      class="SomeClass",
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
                'preHydrate'    => ['class' => 'SomeClass', 'method' => 'preHydrateMethodName']
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
  preHydrate: 
    class: SomeClass
    method: preHydrateMethodName
EOF;
    }
}
