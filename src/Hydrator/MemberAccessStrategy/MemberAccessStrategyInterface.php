<?php

namespace Kassko\DataMapper\Hydrator\MemberAccessStrategy;

use Kassko\DataMapper\ClassMetadata\ClassMetadata;

/**
* Contract for member access strategies.
*
* @author kko
*/
interface MemberAccessStrategyInterface
{
    function prepare($object, ClassMetadata $metadata);
    function getValue($object, $fieldName);
    function setValue($value, $object, $fieldName);
    function setSingleAssociation($subObject, $object, $fieldName);
    function setCollectionAssociation(array $subObjects, $object, $fieldName, $adderPart);
}
