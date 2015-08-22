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
   public function prepare($object, ClassMetadata $metadata);
   public function getValue($object, $fieldName);
   public function setValue($value, $object, $fieldName);
   /**
    * Unused method. To be remove.
    */
   public function setSingleAssociation($subObject, $object, $fieldName);
   /**
    * Unused method. To be remove.
    */
   public function setCollectionAssociation(array $subObjects, $object, $fieldName, $adderPart);
}
