<?php

namespace Kassko\DataAccess\Exception;

use Exception;

/**
 * Create exceptions about error mapping.
 *
 * @author kko
 */
class ObjectMappingException extends Exception
{
	public static function annotationNotFound($annotationName, $fieldName)
	{
		return new self(sprintf("Annotation [%s] not found for object field [%s].", $annotationName, $fieldName));
	}

    public static function notImplementedMethod($funcName)
	{
		return new self(sprintf("You should implement %s.", $funcName));
	}

	public static function connectionParametersNotFound($objectClassName)
	{
		return new self(sprintf("Connection parameters not found for [%s].", $objectClassName));
	}

	public static function invalidObjectClassName($objectClassName)
	{
		return new self(sprintf("Bad object class. Value [%s] given.", $objectClassName));
	}

	public static function invalidDateToWriteToStorage($value, $writeDateFormat)
	{
		return new self(
			sprintf(
				"Invalid date to write to storage. Date given [%s]. Format given [%s].",
				is_object($value) ? get_class($value) : (isset($value) ? $value : 'null'), $writeDateFormat
			)
		);
	}

	public static function mappedFieldNameNotFound($originalFieldName)
	{
		return new self(sprintf("No mapping name found for original field %s.", $originalFieldName));
	}

	public static function originalFieldNameNotFound($mappedFieldName)
	{
		return new self(sprintf("No original name found for mapped field %s.", $mappedFieldName));
	}

	public static function cantCreateDateFromSpecifiedFormat($date, $dateFormat)
	{
		if (is_object($date)) {
			return new self(sprintf("Date should be as a scalar but provided as an object [%s].", get_class($date)));
		}

		return new self(
			sprintf("Cannot create date from given scalar and format. Scalar date given [%s], format given [%s].", $date, $dateFormat)
		);
	}

	public static function valueObjectIdNotFound($valueObjectId)
	{
		return new self(sprintf("Identifier ValueObject [%s] not found.", $valueObjectId));
	}

	public static function invalideDateValueObjectConfig()
	{
		return new self("Invalid date value object configuration.");
	}

	public static function unspecifiedWriteDateFormat()
	{
		return new self("The write date format should be specified.");
	}

	public static function hydrationStrategyAlreadyDefined($mappedFieldName, $actualStrategyClassName, $newStrategyClassName)
	{
		return new self(sprintf("Hydration strategy is already defined for field [%s]. Actual strategy [%s], and new one is [%s].", $mappedFieldName, $actualStrategyClassName, $newStrategyClassName));
	}

	public static function notFoundDriverException($ressource, $type)
	{
		return new self(sprintf('Metadata loading driver not found for ressource "%s" and resource type "%s".', $ressource, $type));
	}

	public static function notFoundAssociationTargetClass($fieldName, $objectClassName)
	{
		return new self(sprintf("'targetClass' not found in association metadata for property %s on class %s.", $fieldName, $objectClassName));
	}

	public static function notFoundAssociationInfo($fieldName, $objectClassName, $infoKey)
	{
		return new self(sprintf("'%s' not found in association metadata for property %s on class %s.", $infoKey, $fieldName, $objectClassName));
	}

	public static function notFoundAssociation($fieldName, $objectClassName)
	{
		return new self(sprintf("No association metadata found for property %s on class %s.", $fieldName, $objectClassName));
	}

	public static function invalidGetter($fieldName, array $config)
	{
		return new self(
                sprintf(
                    'Invalid "getter" configuration for field "%s". Values given : "%s".',
                    $fieldName,
                    '['
                    .implode(
                        ','
                        ,
                        array_map(
                            function($key, $value) {
                                return $key.' => '.$value;
                            },
                            array_keys($config),
                            $config
                        )
                    )
                    .']'
                )
            );
	}

	public static function invalidSetter($fieldName, array $config)
	{
		return new self(
                sprintf(
                    'Invalid "setter" configuration for field "%s". Values given : "%s".',
                    $fieldName,
                    '['
                    .implode(
                        ','
                        ,
                        array_map(
                            function($key, $value) {
                                return $key.' => '.$value;
                            },
                            array_keys($config),
                            $config
                        )
                    )
                    .']'
                )
            );
	}
}