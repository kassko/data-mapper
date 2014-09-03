<?php

namespace Kassko\DataAccess\Query;

use Kassko\DataAccess\ObjectManager;

/**
 * Transform raw results into object representation.
 * And inversely, transform an objet or an object collection into raw results.
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