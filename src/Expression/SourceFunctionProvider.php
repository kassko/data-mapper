<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Expression;

class SourceFunctionProvider
{
    /** @var array<string, mixed> Cache of DataSource results by source id */
    private array $cache = [];
    
    /** @var callable Callback to execute a DataSource by id: function(string $sourceId): mixed */
    private $dataSourceExecutor;

    /**
     * @param callable $dataSourceExecutor Callback to execute a DataSource: function(string $sourceId): mixed
     */
    public function __construct(callable $dataSourceExecutor)
    {
        $this->dataSourceExecutor = $dataSourceExecutor;
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
        if (!isset($this->cache[$sourceId])) {
            $this->cache[$sourceId] = ($this->dataSourceExecutor)($sourceId);
        }
        
        return $this->cache[$sourceId];
    }

    /**
     * Clear the cache (useful for testing or when processing a new object)
     */
    public function clearCache(): void
    {
        $this->cache = [];
    }

    /**
     * Check if a source has been cached
     *
     * @param string $sourceId
     * @return bool
     */
    public function isCached(string $sourceId): bool
    {
        return isset($this->cache[$sourceId]);
    }
}
