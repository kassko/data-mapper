<?php

namespace Kassko\DataMapper\ClassMetadata\Model;

/**
 * This class should not be maintained. Use DataSource instead if you need the next new features.
 *
 * @deprecated
 * @see Provider
 * @author kko
 */
class Provider extends Source
{
    const ON_FAIL_CHECK_RETURN_VALUE = 'checkReturnValue';
    const ON_FAIL_CHECK_EXCEPTION = 'checkException';

    const BAD_RETURN_VALUE_NULL = 'null';
    const BAD_RETURN_VALUE_FALSE = 'false';
    const BAD_RETURN_VALUE_EMPTY_STRING = 'emptyString';
    const BAD_RETURN_VALUE_EMPTY_ARRAY = 'emptyArray';

    /**
     * @var string
     */
    private $id;

    /**
     * @var \Kassko\DataMapper\ClassMetadata\Method
     */
    private $method;

    /**
     * Loading strategy to use for this provider.
     *
     * @var bool
     */
    private $lazyLoading;

    /**
     * How so data contains data for one or severals fields.
     *
     * @var bool
     */
    private $supplySeveralFields;

    /**
     * Ids of sources which provide an intermediate value for a field.
     *
     * @var array
     */
    private $depends = [];

    /**
     * @var string
     *
     * @Enum({"checkReturnValue", "checkException"})
     */
    private $onFail;

    /**
     * @var string
     */
    private $exceptionClass;

    /**
     * @var string
     *
     * @Enum({"null", "false", "emptyString", "emptyArray"})
     */
    private $badReturnValue;

    /**
     * @var string
     */
    private $fallbackSourceId;

    /**
     * @var \Kassko\DataMapper\ClassMetadata\Methods
     */
    private $preprocessors = [];

    /**
     * @var \Kassko\DataMapper\ClassMetadata\Methods
     */
    private $processors = [];

    /**
     * Gets the value of id.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the value of id.
     *
     * @param string $id the id
     *
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Gets the value of method.
     *
     * @return \Kassko\DataMapper\ClassMetadata\Method
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Sets the value of method.
     *
     * @param \Kassko\DataMapper\ClassMetadata\Method $method the method
     *
     * @return self
     */
    public function setMethod(Method $method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Gets the Loading strategy to use for this provider.
     *
     * @return bool
     */
    public function getLazyLoading()
    {
        return $this->lazyLoading;
    }

    /**
     * Sets the Loading strategy to use for this provider.
     *
     * @param bool $lazyLoading the lazy loading
     *
     * @return self
     */
    public function setLazyLoading($lazyLoading)
    {
        $this->lazyLoading = $lazyLoading;

        return $this;
    }

    /**
     * Gets the How so data contains data for one or severals fields.
     *
     * @return bool
     */
    public function getSupplySeveralFields()
    {
        return $this->supplySeveralFields;
    }

    /**
     * Sets the How so data contains data for one or severals fields.
     *
     * @param bool $supplySeveralFields the supply several fields
     *
     * @return self
     */
    public function setSupplySeveralFields($supplySeveralFields)
    {
        $this->supplySeveralFields = $supplySeveralFields;

        return $this;
    }

    /**
     * Gets the Ids of sources which provide an intermediate value for a field.
     *
     * @return array
     */
    public function getDepends()
    {
        return $this->depends;
    }

    /**
     * Sets the Ids of sources which provide an intermediate value for a field.
     *
     * @param array $depends the depends
     *
     * @return self
     */
    public function setDepends(array $depends)
    {
        $this->depends = $depends;

        return $this;
    }

    public function hasDepends()
    {
        return count($this->depends) > 0;
    }

    /**
     * Gets the value of onFail.
     *
     * @return string
     */
    public function getOnFail()
    {
        return $this->onFail;
    }

    /**
     * Sets the value of onFail.
     *
     * @param string $onFail the on fail
     *
     * @return self
     */
    public function setOnFail($onFail)
    {
        $this->onFail = $onFail;

        return $this;
    }

    /**
     * Gets the value of exceptionClass.
     *
     * @return string
     */
    public function getExceptionClass()
    {
        return $this->exceptionClass;
    }

    /**
     * Sets the value of exceptionClass.
     *
     * @param string $exceptionClass the exception class
     *
     * @return self
     */
    public function setExceptionClass($exceptionClass)
    {
        $this->exceptionClass = $exceptionClass;

        return $this;
    }

    /**
     * Gets the value of badReturnValue.
     *
     * @return string
     */
    public function getBadReturnValue()
    {
        return $this->badReturnValue;
    }

    /**
     * Sets the value of badReturnValue.
     *
     * @param string $badReturnValue the bad return value
     *
     * @return self
     */
    public function setBadReturnValue($badReturnValue)
    {
        $this->badReturnValue = $badReturnValue;

        return $this;
    }

    /**
     * Gets the value of fallbackSourceId.
     *
     * @return string
     */
    public function getFallbackSourceId()
    {
        return $this->fallbackSourceId;
    }

    /**
     * Sets the value of fallbackSourceId.
     *
     * @param string $fallbackSourceId the fallback source id
     *
     * @return self
     */
    public function setFallbackSourceId($fallbackSourceId)
    {
        $this->fallbackSourceId = $fallbackSourceId;

        return $this;
    }

    /**
     * Gets the value of preprocessors.
     *
     * @return \Kassko\DataMapper\ClassMetadata\Method[]
     */
    public function getPreprocessors()
    {
        return $this->preprocessors;
    }

    /**
     * Sets the value of preprocessors.
     *
     * @param \Kassko\DataMapper\ClassMetadata\Methods $preprocessor[] the preprocessors
     *
     * @return self
     */
    public function setPreprocessors(array $preprocessors)
    {
        $this->preprocessors = $preprocessors;

        return $this;
    }

    /**
     * Add a preprocessor.
     *
     * @param \Kassko\DataMapper\ClassMetadata\Method[] $preprocessor A preprocessor to add
     *
     * @return self
     */
    public function addPreprocessor(Method $preprocessor)
    {
        $this->preprocessors[] = $preprocessor; 

        return $this;
    }

    /**
     * Gets the value of processors.
     *
     * @return \Kassko\DataMapper\ClassMetadata\Methods
     */
    public function getProcessors()
    {
        return $this->processors;
    }

    /**
     * Sets the value of processors.
     *
     * @param \Kassko\DataMapper\ClassMetadata\Method[] $processors The processors to set
     *
     * @return self
     */
    public function setProcessors(array $processors)
    {
        $this->processors = $processors;

        return $this;
    }

    /**
     * Add a processor.
     *
     * @param \Kassko\DataMapper\ClassMetadata\Method[] $processor A processor to add
     *
     * @return self
     */
    public function addProcessor(Method $processor)
    {
        $this->processors[] = $processor; 
        
        return $this;
    }

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
                throw new \DomainException(sprintf('The bad return value "%s" is not allowed.', $data));
        }
    }
}
