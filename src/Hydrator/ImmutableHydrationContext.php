<?php

namespace Kassko\DataAccess\Hydrator;

use LogicException;

/**
 * Contains the hydration context and lock it.
 * This implementation should be used only internally.
 *
 * @author kko
 *
 * @internal
 */
class ImmutableHydrationContext extends HydrationContext
{
    /**
     * {@inheritdoc}
     */
    public function setData($key, $value)
    {
        throw $this->createReadOnlyContextException();
    }

    /**
     * {@inheritdoc}
     */
    public function removeData($key)
    {
        throw $this->createReadOnlyContextException();
    }

    private function createReadOnlyContextException()
    {
        return new LogicException('The context was already used to hydrate your object. Changes on it will be ignored.');
    }
}
