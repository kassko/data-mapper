<?php

namespace Kassko\DataMapper\Hydrator\HydrationStrategy;

use Kassko\DataMapper\Exception\ObjectMappingException;
use DateTimeInterface;
use DateTime;

/**
* Strategy to hydrate a 'date' field.
*
* @author kko
*/
class DateHydrationStrategy implements HydrationStrategyInterface
{
    private $readDateConverter;
    private $writeDateConverter;
    private $strategyOnNoDate;

    public function __construct($readDateConverter, $writeDateConverter, ClosureHydrationStrategy $strategyOnNoDate = null)
    {
        $this->readDateConverter = $readDateConverter;
        $this->writeDateConverter = $writeDateConverter;
        $this->strategyOnNoDate = $strategyOnNoDate;
    }

    /**
    * {@inheritdoc}
    */
    public function extract($value, $object = null, $data = null)
    {
        if (! isset($this->writeDateConverter)) {
            throw ObjectMappingException::unspecifiedWriteDateFormat();
        }

        if (! isset($value)) {

            if ($this->strategyOnNoDate) {

                return $this->strategyOnNoDate->extract($value, $object, $data);
            }
        }

        if (! $value instanceof DateTimeInterface) {
            throw ObjectMappingException::invalidDateToWriteToStorage($value, $this->writeDateConverter);
        }

        return $value->format($this->writeDateConverter);
    }

    /**
    * {@inheritdoc}
    */
    public function hydrate($value, $data = null, $object = null)
    {
        if (is_null($this->readDateConverter)) {
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

        $mappedValue = DateTime::createFromFormat($this->readDateConverter, $value);
        if (false === $mappedValue) {
            throw ObjectMappingException::cantCreateDateFromSpecifiedFormat($value, $this->readDateConverter);
        }

        return $mappedValue;
    }
}
