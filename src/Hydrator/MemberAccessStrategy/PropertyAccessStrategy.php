<?php

namespace Kassko\DataMapper\Hydrator\MemberAccessStrategy;

use Kassko\DataMapper\ClassMetadata\ClassMetadata;
use Kassko\DataMapper\Hydrator\MemberAccessStrategy\Exception\NotFoundMemberException;
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
        return $this->doGetValue($object, $fieldName);
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

    private function doGetValue($object, $fieldName)
    {
        $reflProperty = $this->getAccessibleProperty($fieldName);
        if (false === $reflProperty) {
            throw new NotFoundMemberException(sprintf('Not found member "%s::%s"', get_class($object), $fieldName));
        }

        return $reflProperty->getValue($object);
    }

    private function doSetValue($fieldName, $object, $value)
    {
        $reflProperty = $this->getAccessibleProperty($fieldName);
        if (false === $reflProperty) {
            return;
        }

        $reflProperty->setValue($object, $value);
    }

    private function getAccessibleProperty($fieldName)
    {
        if (! $this->reflectionClass->hasProperty($fieldName)) {
            return false;
        }

        $reflProperty = $this->reflectionClass->getProperty($fieldName);
        if (! $reflProperty->isPublic()) {
            $reflProperty->setAccessible(true);
        }  

        return $reflProperty;
    }
}
