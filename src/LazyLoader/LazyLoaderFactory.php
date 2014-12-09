<?php

namespace Kassko\DataMapper\LazyLoader;

use Kassko\DataMapper\ObjectManager;

/**
 * Factory for object lazy loader.
 *
 * @author kko
 */
class LazyLoaderFactory implements LazyLoaderFactoryInterface
{
    protected $objectManager;
    private $instances = [];

    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getInstance($objectClass)
    {
        if (! isset($this->instances[$objectClass])) {
            $this->instances[$objectClass] = new LazyLoader($this->objectManager, $objectClass);
        }

        return $this->instances[$objectClass];
    }
}
