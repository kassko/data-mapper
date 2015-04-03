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
    public function all();

    /**
     * Return only one result.
     *
     * @return object
     *
     * @throws NoResultException Throw NoResultException if no result found.
     * @throws NonUniqueResultException Throw NonUniqueResultException if more than one résult found.
     */
    public function single();

    /**
     * Return one result or a specified default result if no result found.
     *
     * @param $defaultResult A default result
     *
     * @return object
     *
     * @throws NonUniqueResultException Throw NonUniqueResultException if more than one result found.
     */
    public function one($defaultResult = null);

    /**
     * Return the first result or a specified default one if no result.
     *
     * @param $defaultResult A default result
     *
     * @return object.
     */
    public function first($defaultResult = null);

    /**
     * Return iterable results.
     *
     * @return Generator
     */
    public function iterable();
}
