<?php

namespace Kassko\DataMapper\LazyLoader;

/**
 * Abstraction for objet lazy loader factory.
 *
 * @author kko
 */
interface LazyLoaderFactoryInterface
{
    /**
     * Get the lazy loader of an entity.
     *
     * @param array $objectClass The entity class
     *
     * @return LazyLoader
     */
    public function getInstance($objectClass);
}
