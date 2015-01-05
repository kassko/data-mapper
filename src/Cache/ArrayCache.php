<?php

namespace Kassko\DataMapper\Cache;

/**
 * A cache implementation which caches in an array.
 * Usefull when we don't use a cache system.
 *
 * @author kko
 */
class ArrayCache implements CacheInterface
{
    /**
     * @var array $data
     */
    private $data = [];

    /**
     * {@inheritdoc}
     */
    public function fetch($id)
    {
        return $this->contains($id) ? $this->data[$id] : false;
    }

    /**
     * {@inheritdoc}
     */
    public function contains($id)
    {
        return isset($this->data[$id]) || array_key_exists($id, $this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function save($id, $data, $lifeTime = 0)
    {
        $this->data[$id] = $data;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        unset($this->data[$id]);

        return true;
    }
}
