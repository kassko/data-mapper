<?php

namespace Kassko\DataAccess\Query;

use Kassko\DataAccess\ObjectManager;

/**
 * Factory for Query.
 *
 * @author kko
 */
class QueryFactory implements QueryFactoryInterface
{
    protected $objectManager;

    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * {@inheritdoc}
     */
    public function createQuery($objectClass)
    {
        return new Query($this->objectManager, $objectClass);
    }
}
