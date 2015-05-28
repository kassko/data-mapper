<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Annotation;

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

}
