<?php

namespace Kassko\DataAccess\Configuration;

use Kassko\DataAccess\ClassMetadata\ClassMetadataFactoryOptionsAwareInterface;

/**
 * Hold configuration.
 *
 * @author kko
 */
class Configuration
{
    private $classMetadataCacheConfig;
    private $resultCacheConfig;

    /**
     * @var string Le type de ressource par défaut pour stocker les métadonnées des classes.
     */
    private $defaultClassMetadataResourceType;

    private $entitiesClassMetadataResourceType = [];
    private $entitiesMappingFile = [];
    private $entitiesMappingDir = [];
    private $entitiesMappingMetadataDir = [];


    /**
     * Sets the value of defaultClassMetadataResourceType.
     *
     * @param string $defaultClassMetadataResourceType Default resource type to store class metadata
     *
     * @return self
     */
    public function setDefaultClassMetadataResourceType($defaultClassMetadataResourceType)
    {
        $this->defaultClassMetadataResourceType = $defaultClassMetadataResourceType;

        return $this;
    }

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

    public function getClassMetadataResource($entityName)
    {
        return isset($this->entitiesClassMetadataResource[$entityName]) ? $this->entitiesClassMetadataResource[$entityName] : null;
    }

    public function getClassMetadataResourceType($entityName)
    {
        return isset($this->entitiesClassMetadataResourceType[$entityName]) ? $this->entitiesClassMetadataResourceType[$entityName] : $this->defaultClassMetadataResourceType;
    }

    public function getClassMetadataDir($entityName)
    {
        return isset($this->entitiesClassMetadataDir[$entityName]) ? $this->entitiesClassMetadataDir[$entityName] : null;
    }

    public function addClassMetadataResourceType($entityName, $entityClassMetadataResourceType)
    {
        $this->entitiesClassMetadataResourceType[$entityName] = $entityClassMetadataResourceType;
        return $this;
    }


    public function addClassMetadataResource($entityName, $entityClassMetadataResource)
    {
        $this->entitiesClassMetadataResource[$entityName] = $entityClassMetadataResource;
        return $this;
    }

    public function addClassMetadataDir($entityName, $entityClassMetadataDir)
    {
        $this->entitiesClassMetadataDir[$entityName] = $entityClassMetadataDir;
        return $this;
    }

    public function visitMetadataFactoryAndSetCache(ClassMetadataFactoryOptionsAwareInterface $classMetadataFactory)
    {
        $classMetadataFactory->setCache($this->classMetadataCacheConfig->getCache());
    }
}