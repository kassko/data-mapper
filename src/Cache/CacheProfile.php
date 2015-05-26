<?php

namespace Kassko\DataMapper\Cache;

/**
 * @author kko
 */
class CacheProfile
{
    private $cacheImpl;
    private $key;
    private $lifetime = 0;

    public function __construct(CacheInterface $cache = null, $key = null, $lifetime = 0)
    {
        $this->cacheImpl = $cache;
        $this->key = $key; 
        $this->lifetime = $lifetime;

        $this->clone = clone $this;
    }

    public function execute(callable $callable)
    {
        if (null === $this->cacheImpl || null === $key || -1 === $this->lifetime) {
            return $callable->__invoke(); 
        }

        if ($this->cacheImpl->contains($this->key)) {
            return $this->cacheImpl->fetch($this->key);
        }

        $result = $callable->__invoke();
        $this->cacheImpl->save($this->key, $result, $this->lifetime);

        return $result;
    }

    public function derive()
    {
        $clone = $this->clone;
        $this->clone = clone $this;

        return $clone;
    }

    /**
     * Sets the value of cacheImpl.
     *
     * @param mixed $cacheImpl the cache impl
     *
     * @return self
     */
    public function setCacheImpl($cacheImpl)
    {
        $this->clone->cacheImpl = $cacheImpl;

        return $this;
    }

    /**
     * Sets the value of key.
     *
     * @param mixed $key the key
     *
     * @return self
     */
    public function setKey($key)
    {
        $this->clone->key = $key;

        return $this;
    }

    /**
     * Sets the value of lifetime.
     *
     * @param mixed $lifetime the lifetime
     *
     * @return self
     */
    public function setLifetime($lifetime)
    {
        $this->clone->lifetime = $lifetime;

        return $this;
    }

    /**
     * Gets the value of cacheImpl.
     *
     * @return mixed
     */
    public function getCacheImpl()
    {
        return $this->cacheImpl;
    }

    /**
     * Gets the value of key.
     *
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Gets the value of lifetime.
     *
     * @return mixed
     */
    public function getLifetime()
    {
        return $this->lifetime;
    }
}
