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
        return $this->doGetValue($fieldName, $object);
    }

    public function setValue($value, $object, $fieldName)
    {
        if (! isset($fieldName)) {
            return;
        }

        $this->doSetValue($fieldName, $object, $value);
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

        $this->doSetValue($fieldName, $object, $subObjectOrCollection);

        return true;
    }

    private function doGetValue($fieldName, $object)
    {
        $reflProperty = $this->reflectionClass->getProperty($fieldName);
        if ($reflProperty->isPrivate()) {
            $reflProperty->setAccessible(true);
        }
        return $reflProperty->getValue($object);
    }

    private function doSetValue($fieldName, $object, $value)
    {
        $reflProperty = $this->reflectionClass->getProperty($fieldName);
        if ($reflProperty->isPrivate()) {
            $reflProperty->setAccessible(true);
        }
        $reflProperty->setValue($object, $value);
    }
}
