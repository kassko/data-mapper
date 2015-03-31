<?php

namespace Kassko\DataMapper\Result;

/**
 * Basic contract for RawResultBuilder.
 *
 * @author kko
 */
interface RawResultBuilderInterface
{
    /**
     * Return raw results from an object representation.
     *
     * @param mixed $result
     *
     * @return array
     */
    public function raw();
}
