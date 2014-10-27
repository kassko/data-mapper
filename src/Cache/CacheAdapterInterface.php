<?php

namespace Kassko\DataAccess\Cache;

/**
 * Contract for cache to adapt to data access cache interface.
 *
 * @author kko
 */
interface CacheAdapterInterface extends CacheInterface
{
    /**
     * @param mixed $wrappedCache The cache to adapt to data access cache interface
     */
    function setWrappedCache($wrappedCache);
}
