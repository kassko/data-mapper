<?php

namespace Kassko\DataMapper;

use Kassko\DataMapper\Query\Query;
use Kassko\DataMapper\ObjectManager;
use Kassko\DataMapper\Result\ResultBuilder;

/**
* DataMapper
*
* @author kko
*/
class DataMapper
{
    protected $objectManager;

    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create a ResultBuilder for the the given object class and data
     *
     * @param mixed $objectClass The fqcn of the object to hydrate
     * @param mixed $data The raw data used to hydrate the object
     *
     * @return ResultBuilder
     */
    public function createResultBuilder($objectClass, $data = null)
    {
        return new ResultBuilder($this->objectManager, $objectClass, $data);
    }

    /**
     * Create a Query for the the given object class.
     *
     * @param $objectClass The class concerned by the query
     *
     * @return Query
     */
    public function createQuery($objectClass)
    {
        return new Query($this->objectManager, $objectClass);
    }

    /**
     * Shortcut to get the configuration
     *
     * @return AbstractConfiguration
     */
    public function getConfiguration()
    {
        return $this->objectManager->getConfiguration();
    }
}