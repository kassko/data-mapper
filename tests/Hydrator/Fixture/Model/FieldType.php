<?php
namespace Kassko\DataMapperTest\Hydrator\Fixture\Model;

use Kassko\DataMapper\Annotation as DM;

class FieldType
{
    /**
     * @DM\Field(type="boolean")
     */
    private $someBool;

    /**
     * @DM\Field(type="integer")
     */
    private $someInt;

    /**
     * @DM\Field(type="float")
     */
    private $someFloat;

    /**
     * @DM\Field(type="string")
     */
    private $someString;

    /**
     * @DM\Field(type="string")
     */
    private $someArray;

    /**
     * Gets the value of someBool.
     *
     * @return mixed
     */
    public function getSomeBool()
    {
        return $this->someBool;
    }

    /**
     * Sets the value of someBool.
     *
     * @param mixed $someBool the some bool
     *
     * @return self
     */
    public function setSomeBool($someBool)
    {
        $this->someBool = $someBool;

        return $this;
    }

    /**
     * Gets the value of someInt.
     *
     * @return mixed
     */
    public function getSomeInt()
    {
        return $this->someInt;
    }

    /**
     * Sets the value of someInt.
     *
     * @param mixed $someInt the some int
     *
     * @return self
     */
    public function setSomeInt($someInt)
    {
        $this->someInt = $someInt;

        return $this;
    }

    /**
     * Gets the value of someFloat.
     *
     * @return mixed
     */
    public function getSomeFloat()
    {
        return $this->someFloat;
    }

    /**
     * Sets the value of someFloat.
     *
     * @param mixed $someFloat the some float
     *
     * @return self
     */
    public function setSomeFloat($someFloat)
    {
        $this->someFloat = $someFloat;

        return $this;
    }

    /**
     * Gets the value of someString.
     *
     * @return mixed
     */
    public function getSomeString()
    {
        return $this->someString;
    }

    /**
     * Sets the value of someString.
     *
     * @param mixed $someString the some string
     *
     * @return self
     */
    public function setSomeString($someString)
    {
        $this->someString = $someString;

        return $this;
    }

    /**
     * Gets the value of someArray.
     *
     * @return mixed
     */
    public function getSomeArray()
    {
        return $this->someArray;
    }

    /**
     * Sets the value of someArray.
     *
     * @param mixed $someArray the some array
     *
     * @return self
     */
    public function setSomeArray($someArray)
    {
        $this->someArray = $someArray;

        return $this;
    }
}
