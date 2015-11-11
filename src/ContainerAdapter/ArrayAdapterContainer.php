<?php

namespace Kassko\DataMapper\ContainerAdapter;

use ArrayAccess;
use Kassko\ClassResolver\ContainerInterface;
use LogicException;

/**
 * @author kko
 */
class ArrayAdapterContainer implements ContainerInterface
{
    private $container;

    public function __construct($container)
    {
        if (! is_array($container) && ! $container instanceof ArrayAccess) {
            throw new LogicException(
                sprintf(
                    'Container should be an array or implements "ArrayAccess". "%s" given.', 
                    is_object($container) ? get_class($container) : gettype($container) 
                )
            );
        }

        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        return $this->container[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function has($name)
    {
        return isset($this->container[$name]) || array_key_exists($name, $this->container);
    }
}
