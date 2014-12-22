<?php

namespace Kassko\DataMapper;

/**
 * Basic contract for DataMapper.
 *
 * @author kko
 */
interface DataMapperInterface
{
    /**
     * Get an Hydrator for the the given object class
     *
     * @param mixed $objectClass The fqcn of the object to hydrate
     * @param mixed $data The raw data used to hydrate the object
     *
     * @return AbstractHydrator
     */
    function hydrator($objectClass);

    /**
     * Get a ResultBuilder for the the given object class and the raw data
     *
     * @param mixed $objectClass The fqcn of the object to hydrate
     * @param mixed $data The raw data used to hydrate the object
     *
     * @return ResultBuilder
     */
    function resultBuilder($objectClass, $data = null);

    /**
     * Create a Query for the given object class.
     *
     * @param $objectClass The class concerned by the query
     *
     * @return Query
     */
    function query($objectClass);

    /**
     * Shortcut to get the configuration
     *
     * @return AbstractConfiguration
     */
    function configuration();
}