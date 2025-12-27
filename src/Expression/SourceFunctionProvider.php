<?php

declare(strict_types=1);

/*
 * This file is part of DataMapper.
 *
 * Copyright 2025 kassko 
 *
 * For the full copyright and license information,
 * please view the LICENSE and NOTICE files that were distributed with this source code.
 */

namespace Kassko\DataMapper\Expression;

use Psr\SimpleCache\CacheInterface;

class SourceFunctionProvider
{
    /** @var callable Callback to execute a DataSource by id: function(string $sourceId): mixed */
    private $dataSourceExecutor;
    
    /** @var CacheInterface|null PSR-16 cache for DataSource results */
    private ?CacheInterface $cache;

    /**
     * @param callable $dataSourceExecutor Callback to execute a DataSource: function(string $sourceId): mixed
     * @param CacheInterface|null $cache Optional PSR-16 cache for storing DataSource results
     */
    public function __construct(callable $dataSourceExecutor, ?CacheInterface $cache = null)
    {
        $this->dataSourceExecutor = $dataSourceExecutor;
        $this->cache = $cache;
    }

    /**
     * Get the result of a DataSource by its id
     * Results are cached to avoid duplicate executions
     *
     * @param string $sourceId
     * @return mixed
     */
    public function getSourceResult(string $sourceId)
    {
        if ($this->cache !== null) {
            $cacheKey = $this->getCacheKey($sourceId);
            
            if ($this->cache->has($cacheKey)) {
                return $this->cache->get($cacheKey);
            }
            
            $result = ($this->dataSourceExecutor)($sourceId);
            $this->cache->set($cacheKey, $result);
            
            return $result;
        }
        
        // No cache provided, execute directly
        return ($this->dataSourceExecutor)($sourceId);
    }

    /**
     * Clear the cache for a specific source or all sources
     *
     * @param string|null $sourceId If null, clears entire cache
     */
    public function clearCache(?string $sourceId = null): void
    {
        if ($this->cache !== null) {
            if ($sourceId !== null) {
                $this->cache->delete($this->getCacheKey($sourceId));
            } else {
                $this->cache->clear();
            }
        }
    }

    /**
     * Check if a source has been cached
     *
     * @param string $sourceId
     * @return bool
     */
    public function isCached(string $sourceId): bool
    {
        if ($this->cache === null) {
            return false;
        }
        
        return $this->cache->has($this->getCacheKey($sourceId));
    }

    /**
     * Generate a cache key for a source ID
     *
     * @param string $sourceId
     * @return string
     */
    private function getCacheKey(string $sourceId): string
    {
        return 'data_mapper_source_' . $sourceId;
    }
}
