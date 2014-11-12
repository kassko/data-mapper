<?php

namespace Kassko\DataAccess\Configuration;

/**
 * A key for an object used to associate it to a configuration.
 *
 * @author kko
 */
class ObjectKey
{
    private $class;
    private $parentClass;
    private $correspondingFieldName;

    public function __construct($class, $parentClass = null, $correspondingFieldName = null)
    {
        $this->class = $class;
        $this->parentClass = $parentClass;
        $this->correspondingFieldName = $correspondingFieldName;
    }

    public function getKey()
    {
        if (null === $this->parentClass) {
            return $this->class;
        }

        return $this->class.$this->parentClass.$this->correspondingFieldName;
    }

    public function getClass()
    {
        return $this->class;
    }
}
