<?php

namespace Kassko\DataAccess\Query;

/**
 * Interface for QueryFactory.
 *
 * @author kko
 */
interface QueryFactoryInterface
{
    /**
     * Create an object Query.
     *
     * @param $objectClass The class concerned by the query
     */
    function createQuery($objectClass);
}
