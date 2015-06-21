<?php
namespace Kassko\DataMapperTest\Hydrator\Fixture\Model;

use Kassko\DataMapper\Annotation as DM;

class BadFieldType
{
    /**
     * @DM\Field(type="unexisting_type")
     */
    private $someType;

    /**
     * Gets the value of someType.
     *
     * @return mixed
     */
    public function getSomeType()
    {
        return $this->someType;
    }

    /**
     * Sets the value of someType.
     *
     * @param mixed $someType the some type
     *
     * @return self
     */
    public function setSomeType($someType)
    {
        $this->someType = $someType;

        return $this;
    }
}
