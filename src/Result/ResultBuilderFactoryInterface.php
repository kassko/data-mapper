<?php

namespace Kassko\DataAccess\Result;

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
    public function createResultBuilder($objectClass, $data = null);
}
