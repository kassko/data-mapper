<?php

namespace Kassko\DataMapper;

/**
 * Abstraction for ResultBuilder factory.
 *
 * @author kko
 */
interface DataMapperInterface
{
    /**
     * Create a ResultBuilder for the the given object class and data
     *
     * @param mixed $objectClass The fqcn of the object to hydrate
     * @param mixed $data The raw data used to hydrate the object
     *
     * @return ResultBuilder
     */
    function createResultBuilder($objectClass, $data = null);

    /**
     * Create a Query for the the given object class.
     *
     * @param $objectClass The class concerned by the query
     *
     * @return Query
     */
    function createQuery($objectClass);
}
