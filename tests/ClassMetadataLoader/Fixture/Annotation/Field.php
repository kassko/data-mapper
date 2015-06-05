<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Annotation;

use Kassko\DataMapper\Annotation as DM;

class Field
{
    /**
     * @DM\Field(
     *      name="FirstField",
     *      type="string",
     *      class="stdClass",
     *      readConverter="readConvertFirstField",
     *      writeConverter="writeConvertFirstField",
     *      fieldMappingExtensionClass="ExtensionClass"
     * )
     */
    protected $fieldOne;

    /**
     * @DM\Field(
     *      name="SecondField",
     *      type="integer",
     *      class="\DateTime",
     *      readDateConverter="readDateConvertSecondField",
     *      writeDateConverter="writeDateConvertSecondField",
     *      fieldMappingExtensionClass="ExtensionClass"
     * )
     */
    protected $fieldTwo;

    /**
     * @DM\Field(
     *      name="DateField",
     *      type="date"
     * )
     */
    protected $dateField;
}
