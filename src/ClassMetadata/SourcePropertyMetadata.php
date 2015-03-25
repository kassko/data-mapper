<?php

namespace Kassko\DataMapper\ClassMetadata;

use DomainException;

class SourcePropertyMetadata
{
	const ON_FAIL_CHECK_RETURN_VALUE = 'checkReturnValue';
	const ON_FAIL_CHECK_EXCEPTION = 'checkException';

	const BAD_RETURN_VALUE_NULL = 'null';
	const BAD_RETURN_VALUE_FALSE = 'false';
	const BAD_RETURN_VALUE_EMPTY_STRING = 'emptyString';
	const BAD_RETURN_VALUE_EMPTY_ARRAY = 'emptyArray';

	public $id; 
	public $class;
	public $method; 
	public $args;
	public $lazyLoading;
	public $supplySeveralFields;

	/**
     * @var string
     *
     * @Enum({self::ON_FAIL_CHECK_RETURN_VALUE, self::ON_FAIL_CHECK_EXCEPTION})
     */
	public $onFail;

    /**
     * @var string
     */
    public $exceptionClass;

    /**
     * @var string
     *
     * @Enum({self::BAD_RETURN_VALUE_NULL, self::BAD_RETURN_VALUE_FALSE, self::BAD_RETURN_VALUE_EMPTY_STRING, self::BAD_RETURN_VALUE_EMPTY_ARRAY})
     */
    public $badReturnValue;

    /**
     * @var string
     */
    public $fallbackSourceId;

    public function areDataInvalid($data) 
    {
    	switch ($this->badReturnValue) {

    		case self::BAD_RETURN_VALUE_NULL:
    			return null === $data;

			case self::BAD_RETURN_VALUE_FALSE:
    			return false === $data;

			case self::BAD_RETURN_VALUE_EMPTY_STRING:
    			return '' === $data;

			case self::BAD_RETURN_VALUE_EMPTY_ARRAY:
    			return empty($data);
    		
    		default:
    			throw new DomainException(sprintf('The bad return value "%s" is not allowed.'));
    	}
    }
}