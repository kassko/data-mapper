<?php

namespace Kassko\DataMapper\Result;

use BadMethodCallException;
use Kassko\DataMapper\Configuration\Configuration;
use Kassko\DataMapper\ObjectManager;
use Kassko\DataMapper\Result\Exception\NoResultException;
use Kassko\DataMapper\Result\Exception\NonUniqueResultException;
use Kassko\DataMapper\Result\Exception\NotFoundIndexException;

/**
 * Transform raw results into object representation.
 *
 * @author kko
 */
class ResultBuilder extends AbstractResultBuilder implements ResultBuilderInterface
{
    protected $objectManager;
    protected $objectClass;
    protected $data;

    public function __construct(ObjectManager $objectManager, $objectClass, array $data)
    {
        $this->objectManager = $objectManager;
        $this->objectClass = $objectClass;
        $this->data = $data;
    }

    public function setRuntimeConfiguration(Configuration $runtimeConfiguration)
    {
        $configuration = $this->objectManager->getConfiguration();
        $configuration->resetConfiguration();
        $configuration->pushRuntimeConfiguration($runtimeConfiguration);
    }

    /**
    * {@inheritdoc}
    */
    public function all()
    {
        return $this->doAll(false);
    }

    /**
    * {@inheritdoc}
    */
    public function single()
    {
        if (0 === count($this->data)) {
            throw new NoResultException($this->objectClass);
        }

        if (count($this->data) > 1 && is_numeric(key($this->data))) {
            throw new NonUniqueResultException($this->objectClass);
        }

        $rh = new ResultHydrator($this->objectManager);
        $object = $rh->hydrate($this->objectClass, $this->data);

        reset($object);

        return current($object);
    }

    /**
    * {@inheritdoc}
    */
    public function one($defaultResult = null)
    {
        if (count($this->data) === 0) {
            return $defaultResult;
        }

        if (count($this->data) > 1 && is_numeric(key($this->data))) {
            throw new NonUniqueResultException($this->objectClass);
        }

        $rh = new ResultHydrator($this->objectManager);
        $object = $rh->hydrate($this->objectClass, $this->data);

        reset($object);

        return current($object);
    }

    /**
    * {@inheritdoc}
    */
    public function first($defaultResult = null)
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
    * {@inheritdoc}
    */
    public function iterable()
    {
        return $this->doIterable(false);
    }

    /**
    * {@inheritdoc}
    */
    protected function doCall($method, $arguments)
    {
        switch (true) {
            case (0 === strpos($method, 'allIndexedBy')):

                $indexOfBy = lcfirst(substr($method, 18));

                $metadata = $this->objectManager->getMetadata($this->objectClass);
                if (! $metadata->existsMappedFieldName($indexOfBy)) {
                    throw new NotFoundIndexException($this->objectClass, $indexOfBy);
                }

                $result = $this->doAll($indexOfBy);
                break;

            case (0 === strpos($method, 'iterableIndexedBy')):

                $indexOfBy = lcfirst(substr($method, 26));

                $metadata = $this->objectManager->getMetadata($this->objectClass);
                if (! $metadata->existsMappedFieldName($indexOfBy)) {
                    throw new NotFoundIndexException($this->objectClass, $indexOfBy);
                }

                $result = $this->doIterable($indexOfBy);
                break;

            default:
                throw new BadMethodCallException("Undefined method '$method'.");
        }

        return $result;
    }

    /**
     * Create an object completely managed by ObjectManager.
     *
     * @return mixed
     */
    public function createObject()
    {
        return new $this->objectClass;
    }

    private function doAll($indexOfBy)
    {
        $rh = new ResultHydrator($this->objectManager);
        $object = $rh->hydrate($this->objectClass, $this->data, $indexOfBy);

        return is_array($object) ? $object : [$object];
    }

    private function doIterable($indexOfBy)
    {
        $rh = new IterableResultHydrator($this->objectManager);

        return $rh->hydrate($this->objectClass, $this->data, $indexOfBy);
    }
}
