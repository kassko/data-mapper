<?php

namespace Kassko\DataAccess\Hydrator\MemberAccessStrategy;

/**
* Access logic by getters/issers/setters to object members to hydrate.
*
* @author kko
*/
class GetterSetterAccessStrategy implements MemberAccessStrategyInterface
{
	private $classMethods;

	public function prepare($object)
	{
		if (! is_object($object)) {
			throw new \InvalidArgumentException(sprintf('Invalid object. An object was expecting but value [%s] given.', is_array($object)?'array':$object));
		}

		$this->classMethods = get_class_methods($object);
	}

	public function getValue($object, $fieldName)
	{
		if (empty($this->classMethods)) {
			return null;
		}

		foreach ($this->getGetterMethods($fieldName) as $currentGetterMethod) {

			if (in_array($currentGetterMethod, $this->classMethods)) {
				$getterMethod = $currentGetterMethod;
				break;
			}
		}

		return isset($getterMethod) ? $object->$getterMethod() : null;
	}

	public function setScalarValue($value, $object, $fieldName)
	{
		$this->classMethods = get_class_methods($object);

		$setter = 'set'.ucfirst($fieldName);
		if (in_array($setter, $this->classMethods)) {
			$object->$setter($value);
		}
	}

	public function setObjectValue($subObjectClassName, $object, $fieldName)
	{
		$setter = 'set'.ucfirst($fieldName);
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
		$setter = 'set'.ucfirst($fieldName);
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

	private function getGetterMethods($fieldName)
	{
		$fieldName = ucfirst($fieldName);

		return [
			'get'.$fieldName,
			'is'.$fieldName,
			'has'.$fieldName,
		];
	}
}