<?php

namespace Kassko\DataMapper\Result;

/**
 * Abstraction for ResultBuilder factory.
 *
 * @author kko
 */
interface ResultBuilderFactoryInterface
{
    /**
     * Create a ResultBuilder
     *
     * @param array $result Raw results
     *
     * @return ResultBuilder
     */
    public function create($objectClass, $data = null);
}
