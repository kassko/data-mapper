<?php

namespace Kassko\DataAccess\Result;

use BadMethodCallException;
use Kassko\DataAccess\ObjectManager;
use Kassko\DataAccess\Result\Exception\NoResultException;
use Kassko\DataAccess\Result\Exception\NonUniqueResultException;

/**
 * Transform raw results into object representation.
 * And inversely, transform an objet or an object collection into raw results.
 *
 * @author kko
 */
class ResultBuilder
{
    private $objectManager;
    private $objectClass;
    private $data;

    public function __construct(ObjectManager $objectManager, $objectClass, $data)
    {
        $this->objectManager = $objectManager;
        $this->objectClass = $objectClass;
        $this->data = $data;
    }

    /**
     * Return all results.
     *
     * @return array
     */
    public function getResult()
    {
        return $this->doGetResult(false);
    }

    /**
     * Return only one result.
     *
     * @return object
     *
     * @throws NoResultException Throw NoResultException if no result found.
     * @throws NonUniqueResultException Throw NonUniqueResultException if more than one résult found.
     */
    public function getSingleResult()
    {
        if (0 === count($this->data)) {
            throw new NoResultException($this->objectClass);
        }

        if (count($this->data) > 1) {
            throw new NonUniqueResultException($this->objectClass);
        }

        $rh = new ResultHydrator($this->objectManager);
        $object = $rh->hydrate($this->objectClass, $this->data);

        reset($object);

        return current($object);
    }

    /**
     * Return one result or null if no result found.
     *
     * @return object
     *
     * @throws NonUniqueResultException Throw NonUniqueResultException if more than one result found.
     */
    public function getOneOrNullResult()
    {
        return $this->getOneOrDefaultResult(null);
    }

    /**
     * Return one result or a specified default result if no result found.
     *
     * @param $defaultResult A default result
     *
     * @return object
     *
     * @throws NonUniqueResultException Throw NonUniqueResultException if more than one result found.
     */
    public function getOneOrDefaultResult($defaultResult)
    {
        if (count($this->data) === 0) {
            return $defaultResult;
        }

        if (count($this->data) > 1) {
            throw new NonUniqueResultException($this->objectClass);
        }

        $rh = new ResultHydrator($this->objectManager);
        $object = $rh->hydrate($this->objectClass, $this->data);

        reset($object);

        return current($object);
    }

    /**
     * Return the first result or null if no result.
     *
     * @return object.
     */
    public function getFirstOrNullResult()
    {
        return $this->getFirstOrDefaultResult(null);
    }

    /**
     * Return the first result or a default one if no result.
     *
     * @param $defaultResult A default result
     *
     * @return object.
     */
    public function getFirstOrDefaultResult($defaultResult)
    {
        if (count($this->data) === 0) {
            return $defaultResult;
        }

        $rh = new ResultHydrator($this->objectManager);
        $object = $rh->hydrate($this->objectClass, $this->data);

        reset($object);

        return current($object);
    }

    /**
     * Return iterable results.
     *
     * @return Generator
     */
    public function getIterableResult()
    {
        return $this->doGetIterableResult(false);
    }

    /**
     * Means getResultIndexedByX() or getIterableResultIndexedByX()
     * where X is the field name to index.
     *
     * Return results as of associative array where key is a field value.
     *
     * @param string The method to call ending by the field to index
     * @param string The method arguments
     *
     * @return array Renvoi un tableau d'objets associatif contenant les objets résultats.
     */
    public function __call($method, $arguments)
    {
        switch (true) {
            case (0 === strpos($method, 'getResultIndexedBy')):

                $indexOfBy = lcfirst(substr($method, 18));
                $result = $this->doGetResult($indexOfBy);
                break;

            case (0 === strpos($method, 'getIterableResultIndexedBy')):

                $indexOfBy = lcfirst(substr($method, 26));
                $result = $this->doGetIterableResult($indexOfBy);
                break;

            default:
                throw new BadMethodCallException("Undefined method '$method'.");
        }

        return $result;
    }

    /**
     * Return raw results from an object representation.
     *
     * @param mixed $result
     *
     * @return array
     */
    public function getRawResult()
    {
        $rh = new ResultExtractor($this->objectManager);

        return $rh->extract($this->objectClass, $this->data);
    }

    private function doGetResult($indexOfBy)
    {
        $rh = new ResultHydrator($this->objectManager);
        $object = $rh->hydrate($this->objectClass, $this->data, $indexOfBy);

        return is_array($object) ? $object : [$object];
    }

    private function doGetIterableResult($indexOfBy)
    {
        $rh = new IterableResultHydrator($this->objectManager);

        return $rh->hydrate($this->objectClass, $this->data, $indexOfBy);
    }
}
