<?php
namespace Kassko\DataMapperTest\Hydrator\MemberAccessStrategy\Fixture\Model;

use Kassko\DataMapper\Annotation as DM;

class ClassWithOnlyProperties
{
    public $propertyA;
    /**
     * @DM\Field(type="integer")
     */
    protected $propertyB;
    /**
     * @DM\Field(class="Kassko\DataMapperTest\Hydrator\MemberAccessStrategy\Fixture\Model\SampleClass")
     */
    private $propertyC;
}
