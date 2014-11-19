<?php

namespace Kassko\DataAccess\Configuration;

use Kassko\DataAccess\ClassMetadata\ClassMetadataFactoryOptionsAwareInterface;
use RuntimeException;

/**
 * Contains configuration.
 *
 * @author kko
 */
class Configuration extends AbstractConfiguration
{
    /**
     * @var CacheConfiguration The class metadata cache config
     */
    protected $classMetadataCacheConfig;

    /**
     * @var CacheConfiguration The result cache config_d
     */
    protected $resultCacheConfig;


    public function getClassMetadataCacheConfig()
    {
        return $this->classMetadataCacheConfig;
    }

    public function setClassMetadataCacheConfig(CacheConfiguration $classMetadataCacheConfig)
    {
        $this->classMetadataCacheConfig = $classMetadataCacheConfig;
        return $this;
    }

    public function getResultCacheConfig()
    {
        return $this->resultCacheConfig;
    }

    public function setResultCacheConfig(CacheConfiguration $resultCacheConfig)
    {
        $this->resultCacheConfig = $resultCacheConfig;
        return $this;
    }

    public function visitMetadataFactoryAndSetCache(ClassMetadataFactoryOptionsAwareInterface $classMetadataFactory)
    {
        $classMetadataFactory->setCache($this->classMetadataCacheConfig->getCache());
    }
}
