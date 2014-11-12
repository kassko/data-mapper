<?php

namespace Kassko\DataAccess\Hydrator\MemberAccessStrategy;

use Kassko\DataAccess\ClassMetadata\ClassMetadata;

/**
* Access logic by getters/issers/setters to object members to hydrate.
*
* @author kko
*/
class GetterSetterAccessStrategy implements MemberAccessStrategyInterface
{
    private $classMethods;
    private $classMetadata;

    public function prepare($object, ClassMetadata $classMetadata)
    {
        if (! is_object($object)) {
            throw new \InvalidArgumentException(sprintf('Invalid object. An object was expecting but value [%s] given.', is_array($object)?'array':$object));
        }

        $this->classMethods = get_class_methods($object);
        $this->classMetadata = $classMetadata;
    }

    public function getValue($object, $fieldName)
    {
        if (empty($this->classMethods)) {
            return null;
        }

        $getter = $this->classMetadata->getterise($fieldName);

        return isset($getter) && in_array($setter, $this->classMethods) ? $object->$getter() : null;
    }

    public function setScalarValue($value, $object, $fieldName)
    {
        $setter = $this->classMetadata->setterise($fieldName);

        if (isset($setter) && in_array($setter, $this->classMethods)) {
            $object->$setter($value);
        }
    }

    public function setObjectValue($subObjectClassName, $object, $fieldName)
    {
        $setter = $this->classMetadata->setterise($fieldName);

        if (in_array($setter, $this->classMethods)) {
            if ('DateTime' != $subObjectClassName) {
                $value = new $subObjectClassName;
            } else {
                $value = new \DateTime;
            }
            $object->$setter($value);

            return $value;
        }

        return false;
    }

    public function setSingleAssociation($subObject, $object, $fieldName)
    {
        $setter = $this->classMetadata->setterise($fieldName);
        if (in_array($setter, $this->classMethods)) {

            $object->$setter($subObject);
            return true;
        }

        return false;
    }

    public function setCollectionAssociation(array $subObjects, $object, $fieldName, $adderPart)
    {
        $adder = 'add'.ucfirst($adderPart);

        if (in_array($adder, $this->classMethods)) {

            foreach ($subObjects as $subObject) {
                $object->$adder($subObject);
            }

            return true;
        }

        return false;
    }
}
