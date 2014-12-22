<?php

namespace Kassko\DataMapper\Result;

/**
 * Base for ResultBuilder.
 * Enforce a contract with the magic __call method.
 *
 * @author kko
 */
abstract class AbstractResultBuilder implements ResultBuilderInterface
{
    public function __call($method, $arguments)
    {
        return $this->doCall($method, $arguments);
    }

    /**
     * Means getResultIndexedByX() or getIterableResultIndexedByX()
     * where X is the field name to index.
     *
     * Return results as of associative array where key is a field value.
     *
     * @param string The method to call ending by the field to index
     * @param string The method arguments
     *
     * @return array Renvoi un tableau d'objets associatif contenant les objets résultats.
     */
    abstract protected function doCall($method, $arguments);
}