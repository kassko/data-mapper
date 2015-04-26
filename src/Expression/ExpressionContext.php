<?php

namespace Kassko\DataMapper\Expression;

use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;
use RuntimeException;

/**
* ExpressionContext
*
* @author kko
*/
class ExpressionContext implements ArrayAccess, IteratorAggregate
{
    /**
     * Contains all the context variables.
     * @var array
     */
    private $variables;

    /**
     * Return all the context variables.
     * @return array
     */
    public function getData()
    {
    	return $this->variables;
    }

    /**
     * Add a variable in the context.
     *
     * @param string $key A key to store the variable.
     * @param mixed $value The variable to store.
     */
    public function addVariable($key, $value)
    {
        if (is_null($key) || ! is_string($key)) {
            throw new RuntimeException(sprintf('The key where to save your variable in the expression context is invalid. Got "%s".', $key));
        }

        $this->variables[$key] = $value;
    }

    public function flush()
    {
        $this->variables = [];
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($key)
    {
        return array_key_exists($key, $this->variables);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($key)
    {
        if ($this->offsetExists($key)) {
            return $this->variables[$key];
        }

        throw new RuntimeException(sprintf('No variable registered on key "%s" in expression context.', $key));
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        $this->addVariable($offset, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($key)
    {
        unset($this->variables[$key]);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator() {
        return new ArrayIterator($this->variables);
    }
}
