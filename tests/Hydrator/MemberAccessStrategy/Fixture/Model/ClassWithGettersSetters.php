<?php
namespace Kassko\DataMapperTest\Hydrator\MemberAccessStrategy\Fixture\Model;

use Kassko\DataMapper\Annotation as DM;
use Kassko\DataMapperTest\Hydrator\MemberAccessStrategy\Fixture\Model\SampleClass;

class ClassWithGettersSetters
{
    private $propertyA;

    /**
     * @DM\Field(type="integer")
     */
    private $propertyB;
    
    /**
     * @DM\Field(class="Kassko\DataMapperTest\Hydrator\MemberAccessStrategy\Fixture\Model\SampleClass")
     */
    private $propertyC;

    private $propertyWithNoGetterSetter;

    /**
     * Gets the value of propertyA.
     *
     * @return mixed
     */
    public function getPropertyA()
    {
        return $this->propertyA;
    }

    /**
     * Sets the value of propertyA.
     *
     * @param mixed $propertyA the property
     *
     * @return self
     */
    public function setPropertyA($propertyA)
    {
        $this->propertyA = $propertyA;

        return $this;
    }

    /**
     * Gets the value of propertyB.
     *
     * @return mixed
     */
    public function getPropertyB()
    {
        return $this->propertyB;
    }

    /**
     * Sets the value of propertyB.
     *
     * @param mixed $propertyB the property
     *
     * @return self
     */
    public function setPropertyB($propertyB)
    {
        $this->propertyB = $propertyB;

        return $this;
    }

    /**
     * Gets the value of propertyC.
     *
     * @return SampleClass
     */
    public function getPropertyC()
    {
        return $this->propertyC;
    }

    /**
     * Sets the value of propertyC.
     *
     * @param SampleClass $propertyC the property
     *
     * @return self
     */
    public function setPropertyC(SampleClass $propertyC)
    {
        $this->propertyC = $propertyC;

        return $this;
    }
}
