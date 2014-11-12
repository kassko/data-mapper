<?php

namespace Kassko\DataAccess\Query;

use Kassko\DataAccess\Configuration\Configuration;
use Kassko\DataAccess\Cache\CacheInterface;

/**
* Hold user cache settings.
*
* @author kko
*/
class CacheParam
{
    private $cache;

    /**
     * @var mixed For fluent tree interface
     */
    private $parent;
    private $enabled;
    private $key;
    private $lifeTime;
    private $shared;

    public function __construct(Configuration $configuration, $parent)
    {
        $cacheConfig = $configuration->getResultCacheConfig();

        $this->enabled = $cacheConfig->isEnabled();
        $this->lifeTime = $cacheConfig->getLifeTime();
        $this->cache = $cacheConfig->getCache();
        $this->shared = $cacheConfig->isShared();
        $this->parent = $parent;
    }

    public function end()
    {
        return $this->parent;
    }

    public function setParam($key, $lifeTime = 0, $cache = null, $shared = null)
    {
        $this->key = $key;

        if (0 !== $lifeTime) {
            $this->lifeTime = $lifeTime;
        }

        if (null !== $shared) {
            $this->shared = $shared;
        }

        if ($cache) {
            $this->cache = $cache;
        }

        $this->enabled = $use;

        return $this;
    }

    public function isEnabled()
    {
        return $this->enabled;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    public function getLifeTime()
    {
        return $this->lifeTime;
    }

    public function setLifeTime($lifeTime)
    {
        $this->lifeTime = $lifeTime;
        return $this;
    }

    public function isShared()
    {
        return $this->shared;
    }

    public function setShared($shared)
    {
        $this->shared = $shared;
        return $this;
    }

    public function getCache()
    {
        return $this->cache;
    }

    public function setCache(CacheInterface $cache)
    {
        $this->cache = $cache;
        return $this;
    }
}
