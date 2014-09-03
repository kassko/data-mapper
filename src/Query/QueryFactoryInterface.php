<?php

namespace Kassko\DataAccess\Query;

/**
 * Abstraction of QueryFactory.
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