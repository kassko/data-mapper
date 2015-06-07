<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Metadata;

use Kassko\DataMapper\Annotation as DM;

/**
 * @DM\Object(
 *      fieldExclusionPolicy="exclude_all",
 *      providerClass="testProviderClass",
 *      readDateConverter="testReadDateConverter",
 *      writeDateConverter="testWriteDateConverter",
 *      propertyAccessStrategy=true,
 *      fieldMappingExtensionClass="testFieldMappingExtensionClass",
 *      classMappingExtensionClass="testClassMappingExtensionClass"
 * )
 */
class Object
{
    /**
     * @return array
     */
    public static function loadInnerPhpMetadata()
    {
        return [
            'object'    => [
                'fieldExclusionPolicy'  => 'exclude_all',
                'providerClass'         => 'testProviderClass',
                'readDateConverter'     => 'testReadDateConverter',
                'writeDateConverter'    => 'testWriteDateConverter',
                'propertyAccessStrategy'=> true,
                'fieldMappingExtensionClass' => 'testFieldMappingExtensionClass',
                'classMappingExtensionClass' => 'testClassMappingExtensionClass'
            ]
        ];
    }
}
