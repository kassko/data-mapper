<?php

namespace Kassko\DataMapper\Result;

/**
 * Basic contract for ResultBuilder.
 *
 * @author kko
 */
interface ResultBuilderInterface
{
    /**
     * Return all results.
     *
     * @return array
     */
    function all();

    /**
     * Return only one result.
     *
     * @return object
     *
     * @throws NoResultException Throw NoResultException if no result found.
     * @throws NonUniqueResultException Throw NonUniqueResultException if more than one résult found.
     */
    function single();

    /**
     * Return one result or a specified default result if no result found.
     *
     * @param $defaultResult A default result
     *
     * @return object
     *
     * @throws NonUniqueResultException Throw NonUniqueResultException if more than one result found.
     */
    function one($defaultResult = null);

    /**
     * Return the first result or a specified default one if no result.
     *
     * @param $defaultResult A default result
     *
     * @return object.
     */
    function first($defaultResult = null);

    /**
     * Return iterable results.
     *
     * @return Generator
     */
    function iterable();

    /**
     * Return raw results from an object representation.
     *
     * @param mixed $result
     *
     * @return array
     */
    function raw();
}
