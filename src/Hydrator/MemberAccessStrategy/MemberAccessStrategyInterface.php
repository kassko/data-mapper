<?php

namespace Kassko\DataAccess\Hydrator\MemberAccessStrategy;

use Kassko\DataAccess\ClassMetadata\ClassMetadata;

/**
* Contract for member access strategies.
*
* @author kko
*/
interface MemberAccessStrategyInterface
{
	function prepare($object, ClassMetadata $metadata);
	function getValue($object, $fieldName);
	function setScalarValue($value, $object, $fieldName);
	function setObjectValue($subObjectClassName, $object, $fieldName);
    function setSingleAssociation($subObject, $object, $fieldName);
    function setCollectionAssociation(array $subObjects, $object, $fieldName, $adderPart);
}