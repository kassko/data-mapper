<?php

namespace Kassko\DataMapper\ContainerAdapter;

use Kassko\ClassResolver\ContainerInterface;
use LogicException;

/**
 * @author kko
 */
class VariableAdapterContainer implements ContainerInterface
{
    private $container;
    private $getMethodName;
    private $hasMethodName;

    public function __construct($container, $getMethodName, $hasMethodName)
    {
        if (! is_object($container)) {
            throw new LogicException(sprintf('Container should be an object. Type "%s" given.', gettype($container)));
        }

        $this->container = $container;
        $this->getMethodName = $getMethodName;
        $this->hasMethodName = $hasMethodName;
    }

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        return $this->container->{$this->getMethodName}($name);
    }

    /**
     * {@inheritdoc}
     */
    public function has($name)
    {
        return $this->container->{$this->hasMethodName}($name);
    }
}
