<?php

namespace Kassko\DataAccess\Cache;

/**
 * Contract for cache to adapt to data access cache interface.
 *
 * @author kko
 */
abstract class CacheAdapter implements CacheInterface
{
    protected $wrappedCache;

    /**
     * @param mixed $wrappedCache The cache to adapt to data access cache interface
     */
    public function setWrappedCache($wrappedCache)
    {
        $this->wrappedCache = $wrappedCache;
    }
}
