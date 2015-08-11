<?php
namespace Kassko\DataMapperTest\Hydrator\Fixture\Model;

use Kassko\DataMapper\Annotation as DM;

class FieldClass
{
    /**
     * @DM\Field(class="Kassko\DataMapperTest\Hydrator\Fixture\Model\NestedClass")
     */
    public $property;
    /**
     * @DM\Field(class="Kassko\DataMapperTest\Hydrator\Fixture\Model\NestedClass")
     */
    public $collectionProperty;
    /**
     * @DM\Field(class="Kassko\DataMapperTest\Hydrator\Fixture\Model\NestedClass")
     */
    public $alreadyHydratedProperty;
}
