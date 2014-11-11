<?php

namespace Kassko\DataAccess\Configuration;

use RuntimeException;

/**
 * Base for original configuration and runtime configurations.
 *
 * @author kko
 */
abstract class AbstractConfiguration
{
    /**
     * @var Default resource type to store class metadata
     */
    protected $defaultClassMetadataResourceType;
    /**
     * @var Default resource dir in witch store class metadata
     */
    protected $defaultClassMetadataResourceDir;
    protected $entitiesClassMetadataResourceType = [];
    protected $entitiesClassMetadataResource = [];
    protected $entitiesClassMetadataDir = [];


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

    /**
     * Sets the value of defaultClassMetadataResourceDir.
     *
     * @param string $defaultClassMetadataResourceDir Default resource type to store class metadata
     *
     * @return self
     */
    public function setDefaultClassMetadataResourceDir($defaultClassMetadataResourceDir)
    {
        $this->defaultClassMetadataResourceDir = $defaultClassMetadataResourceDir;
        return $this;
    }

    public function addClassMetadataResourceType($objectName, $entityClassMetadataResourceType)
    {
        $this->entitiesClassMetadataResourceType[$objectName] = $entityClassMetadataResourceType;
        return $this;
    }

    public function addClassMetadataResource($objectName, $entityClassMetadataResource)
    {
        $this->entitiesClassMetadataResource[$objectName] = $entityClassMetadataResource;
        return $this;

        /*
        $realPath = null;
        $info = pathinfo($entityClassMetadataResource);
        if ('.' !== $info['dirname']) {
            $realPath = $entityClassMetadataResource;
        } else {
            $realPath = $this->defaultClassMetadataResourceDir.'/'.$entityClassMetadataResource;
        }

        //return $realPath;

        //$path = $this->defaultClassMetadataResourceDir.'/'.$entityClassMetadataResource;
        $this->entitiesClassMetadataResource[$objectName] = $realPath;
        return $this;
        */
    }

    public function getClassMetadataDir($objectName)
    {
        return isset($this->entitiesClassMetadataDir[$objectName]) ? $this->entitiesClassMetadataDir[$objectName] : null;
    }

    public function addClassMetadataDir($objectName, $entityClassMetadataDir)
    {
        $this->entitiesClassMetadataDir[$objectName] = $entityClassMetadataDir;
        return $this;
    }

    public function addMappingResourceInfo($mrck, $resource, $resourceType)
    {
        if ($mrck instanceof ObjectKey) {

            $this->addClassMetadataResource($mrck->getKey(), $resource);
            $this->addClassMetadataResourceType($mrck->getKey(), $resourceType);
        } elseif (is_string($mrck)) {

            $this->addClassMetadataResource($mrck, $resource);
            $this->addClassMetadataResourceType($mrck, $resourceType);
        } else {

            throw new RuntimeException(
                sprintf(
                    'Type string or instance of "%s" expected and got "%s"',
                    ObjectKey::class,
                    is_object($mrck) ? get_class($mrck) : gettype($mrck)
                )
            );
        }

        return $this;
    }

    public function getClassMetadataResource($objectName)
    {
        if (isset($this->entitiesClassMetadataResource[$objectName])) {

            $realPath = null;
            $info = pathinfo($this->entitiesClassMetadataResource[$objectName]);
            if ('.' !== $info['dirname']) {
                $realPath = $this->entitiesClassMetadataResource[$objectName];
            } else {
                $realPath = $this->defaultClassMetadataResourceDir.'/'.$this->entitiesClassMetadataResource[$objectName];
            }

            return $realPath;
        }

        return null;

        /*
        return isset($this->entitiesClassMetadataResource[$objectName]) ?
            $this->entitiesClassMetadataResource[$objectName]:
            null
        ;
        */
    }

    public function getClassMetadataResourceType($objectName)
    {
        return isset($this->entitiesClassMetadataResourceType[$objectName]) ? $this->entitiesClassMetadataResourceType[$objectName] : $this->defaultClassMetadataResourceType;
    }
}
