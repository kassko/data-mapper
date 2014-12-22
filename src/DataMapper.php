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
class DataMapper implements DataMapperInterface
{
    protected $objectManager;

    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
    * {@inheritdoc}
    */
    public function hydrator($objectClass)
    {
        return $this->objectManager->getHydratorFor($objectClass);
    }

    /**
    * {@inheritdoc}
    */
    public function resultBuilder($objectClass, $data = null)
    {
        return new ResultBuilder($this->objectManager, $objectClass, $data);
    }

    /**
    * {@inheritdoc}
    */
    public function query($objectClass)
    {
        return new Query($this->objectManager, $objectClass);
    }

    /**
    * {@inheritdoc}
    */
    public function configuration()
    {
        return $this->objectManager->getConfiguration();
    }
}