<?php

namespace Kassko\DataMapper\Hydrator\MemberAccessStrategy;

use Kassko\DataMapper\ClassMetadata\ClassMetadata;

/**
* Access logic by getters/issers/setters to object members to hydrate.
*
* @author kko
*/
class GetterSetterAccessStrategy implements MemberAccessStrategyInterface
{
    private $propertyAccessStrategy;
    private $classMethods;
    private $classMetadata;

    public function __construct(PropertyAccessStrategy $propertyAccessStrategy)
    {
        $this->propertyAccessStrategy = $propertyAccessStrategy;
    }

    public function prepare($object, ClassMetadata $classMetadata)
    {
        if (! is_object($object)) {
            throw new \InvalidArgumentException(sprintf('Invalid object. An object was expecting but value [%s] given.', is_array($object)?'array':$object));
        }

        $this->classMethods = get_class_methods($object);
        $this->classMetadata = $classMetadata;

        //$this->propertyAccessStrategy->prepare($object, $classMetadata);
    }

    public function getValue($object, $fieldName)
    {
        if (! empty($this->classMethods)) {
            $getter = $this->classMetadata->getterise($fieldName);
            if (isset($getter) && in_array($getter, $this->classMethods)) {
                return $object->$getter();
            };
        }

        return $this->propertyAccessStrategy->getValue($object, $fieldName);
    }

    public function setValue($value, $object, $fieldName)
    {
        $setter = $this->classMetadata->setterise($fieldName);

        if (isset($setter) && in_array($setter, $this->classMethods)) {
            $object->$setter($value);
        } else {
            $this->propertyAccessStrategy->setValue($value, $object, $fieldName);
        }
    }

    public function setSingleAssociation($subObject, $object, $fieldName)
    {
        $setter = $this->classMetadata->setterise($fieldName);
        if (in_array($setter, $this->classMethods)) {
            $object->$setter($subObject);
            return true;
        }

        return $this->propertyAccessStrategy->setSingleAssociation($subObject, $object, $fieldName);
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

        $setter = $this->classMetadata->setterise($fieldName);
        if (isset($setter) && in_array($setter, $this->classMethods)) {
            $object->$setter($subObjects);
        }

        return $this->propertyAccessStrategy->setCollectionAssociation($subObjects, $object, $fieldName, $adderPart);
    }
}
