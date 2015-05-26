<?php

namespace Kassko\DataMapper\Hydrator;

class CurrentHydrationContext
{
    /**
     * @var array
     */
    private $data;
    /**
     * @var mixed
     */
    private $object;

    public function __construct($data, $object)
    {
        $this->data = $data; 
        $this->object = $object;
    }

    /**
     * Gets the value of data.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Gets the value of object.
     *
     * @return mixed
     */
    public function getObject()
    {
        return $this->object;
    }
}
