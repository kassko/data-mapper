<?php

namespace Kassko\DataAccess\Hydrator\MemberAccessStrategy;

/**
* Contract for member access strategies.
*
* @author kko
*/
interface MemberAccessStrategyInterface
{
	function prepare($object);
	function getValue($object, $fieldName);
	function setScalarValue($value, $object, $fieldName);
	function setObjectValue($subObjectClassName, $object, $fieldName);
    function setSingleAssociation($subObject, $object, $fieldName);
    function setCollectionAssociation(array $subObjects, $object, $fieldName, $adderPart);
}