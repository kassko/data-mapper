<?php

namespace Kassko\DataAccess\Hydrator\HydrationStrategy;

use Kassko\DataAccess\Exception\ObjectMappingException;
use DateTimeInterface;
use DateTime;

/**
* Strategy to hydrate a 'date' field.
*
* @author kko
*/
class DateHydrationStrategy implements HydrationStrategyInterface
{
    private $readDateFormat;
    private $writeDateFormat;
    private $strategyOnNoDate;

    public function __construct($readDateFormat, $writeDateFormat, ClosureHydrationStrategy $strategyOnNoDate = null)
    {
        $this->readDateFormat = $readDateFormat;
        $this->writeDateFormat = $writeDateFormat;
        $this->strategyOnNoDate = $strategyOnNoDate;
    }

    /**
    * {@inheritdoc}
    */
    public function extract($value, $object = null, $data = null)
    {
        if (! isset($this->writeDateFormat)) {
            throw ObjectMappingException::unspecifiedWriteDateFormat();
        }

        if (! isset($value)) {

            if ($this->strategyOnNoDate) {

                return $this->strategyOnNoDate->extract($value, $object, $data);
            }
        }

        if (! $value instanceof DateTimeInterface) {
            throw ObjectMappingException::invalidDateToWriteToStorage($value, $this->writeDateFormat);
        }

        return $value->format($this->writeDateFormat);
    }

    /**
    * {@inheritdoc}
    */
    public function hydrate($value, $data = null, $object = null)
    {
        if (is_null($this->readDateFormat)) {
            return $value;
        }

        if (! isset($value)) {

            if ($this->strategyOnNoDate) {

                return $this->strategyOnNoDate->hydrate($value, $data, $object);
            }
        }

        if ($value instanceof DateTimeInterface) {
            return $value;
        }

        $mappedValue = DateTime::createFromFormat($this->readDateFormat, $value);
        if (false === $mappedValue) {
            throw ObjectMappingException::cantCreateDateFromSpecifiedFormat($value, $this->readDateFormat);
        }

        return $mappedValue;
    }
}
