<?php

namespace Kassko\DataMapper\Hydrator\MemberAccessStrategy;

use Kassko\DataMapper\ClassMetadata\ClassMetadata;
use ReflectionClass;

/**
* Access logic by property to object members to hydrate.
*
* @author kko
*/
class PropertyAccessStrategy implements MemberAccessStrategyInterface
{
    private $reflectionClass;

    public function prepare($object, ClassMetadata $metadata)
    {
        $this->reflectionClass =  $metadata->getReflectionClass();
    }

    public function getValue($object, $fieldName)
    {
        $reflProperty = $this->reflectionClass->getProperty($fieldName);
        $reflProperty->setAccessible(true);

        return $reflProperty->getValue($object);
    }

    public function setValue($value, $object, $fieldName)
    {
        if (! isset($fieldName)) {
            return;
        }

        $reflProperty = $this->reflectionClass->getProperty($fieldName);
        $reflProperty->setAccessible(true);
        $reflProperty->setValue($object, $value);
    }

    public function setSingleAssociation($subObject, $object, $fieldName)
    {
        return $this->setAssociation($subObject, $object, $fieldName);
    }

    public function setCollectionAssociation(array $subObjects, $object, $fieldName, $adderPart)
    {
        return $this->setAssociation($subObjects, $object, $fieldName);
    }

    private function setAssociation($subObjectOrCollection, $object, $fieldName)
    {
        if (! isset($fieldName)) {
            return false;
        }

        $reflProperty = $this->reflectionClass->getProperty($fieldName);
        $reflProperty->setAccessible(true);
        $reflProperty->setValue($object, $subObjectOrCollection);

        return true;
    }
}
