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
     * {@inheritdoc}
     */
    public function createResultBuilder($objectClass, $data = null)
    {
        return new ResultBuilder($this->objectManager, $objectClass, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function createQuery($objectClass)
    {
        return new Query($this->objectManager, $objectClass);
    }
}