<?php

namespace Kassko\DataAccess\Registry;

use RuntimeException;

/**
 * Registry
 *
 * @author kko
 */
final class UserRegistry implements \ArrayAccess, \IteratorAggregate
{
    /**
     * Hold user data
     * @var array
     */
    private $userRegistry = [];

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
        return array_key_exists($key, $this->userRegistry);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($key)
    {
        if ($this->exists($key)) {
            return $this->userRegistry[$key];
        }

        throw new RuntimeException(sprintf('No data registered on key "%s" in the registry.', $key));
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value, $canOverride = true)
    {
        if (is_null($offset)) {
            throw new RuntimeException(sprintf('You should specify an index "%s" where to save in the registry.', $offset));
        }

        if (! $canOverride && $this->exists($offset)) {
            throw new RuntimeException(sprintf('The key "%s" cannot be overriden in the registry.', $offset));
        }

        $this->userRegistry[$offset] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($key)
    {
        unset($this->userRegistry[$key]);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator() {
        return new \ArrayIterator($this->userRegistry);
    }

    private function __construct() {}

    private function __clone() {}
}
