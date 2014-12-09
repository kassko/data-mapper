<?php

namespace Kassko\DataMapper\Query;

use Kassko\DataMapper\ObjectManager;

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
