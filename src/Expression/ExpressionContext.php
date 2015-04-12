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
    private $context;

    /**
     * Return all the context variables.
     * @return array
     */
    public function getData()
    {
    	return $this->context;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($key)
    {
        return array_key_exists($key, $this->context);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($key)
    {
        if ($this->offsetExists($key)) {
            return $this->context[$key];
        }

        throw new RuntimeException(sprintf('No data registered on key "%s" in expression context.', $key));
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            throw new RuntimeException(sprintf('You should specify an index "%s" where to save in the expression context.', $offset));
        }

        $this->context[$offset] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($key)
    {
        unset($this->context[$key]);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator() {
        return new ArrayIterator($this->context);
    }

    public function flush()
    {
        $this->context = [];
    }
}
