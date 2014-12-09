<?php

namespace Kassko\DataMapper\Registry;

use RuntimeException;

/**
 * Registry
 *
 * @author kko
 */
final class Registry implements \ArrayAccess, \IteratorAggregate
{
    const KEY_LAZY_LOADER_FACTORY = 'lazy_loader_factory';
    const KEY_LOGGER = 'logger';

    /**
     * Contains data
     * @var array
     */
    private $registry = [];

    public static function getInstance()
    {
        static $instance;

        if (null === $instance) {
            $instance = new self;
        }

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($key)
    {
        return array_key_exists($key, $this->registry);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($key)
    {
        if ($this->offsetExists($key)) {
            return $this->registry[$key];
        }

        throw new RuntimeException(sprintf('No data registered on key "%s" in the registry.', $key));
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            throw new RuntimeException(sprintf('You should specify an index "%s" where to save in the registry.', $offset));
        }

        if ($this->offsetExists($offset)) {
            throw new RuntimeException(sprintf('The key "%s" cannot be overriden in the registry.', $offset));
        }

        $this->registry[$offset] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($key)
    {
        unset($this->registry[$key]);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator() {
        return new \ArrayIterator($this->registry);
    }

    private function __construct() {}

    private function __clone() {}
}
