<?php

namespace Kassko\DataMapper\ClassMetadataLoader;

use Kassko\DataMapper\Configuration\Configuration;
use Kassko\DataMapper\Configuration\ObjectKey;

/**
 * Contains criteria to retrieve metadata and their location.
 *
 * @author kko
 */
class LoadingCriteria implements LoadingCriteriaInterface
{
    /**
     *@var string the resource path
     */
    private $resourcePath;

    /**
     *@var string the resource type
     */
    private $resourceType;

    /**
     *@var string the class provider
     */
    private $resourceClass;

    /**
     *@var string the method provider
     */
    private $resourceMethod = 'provideMapping';


    private function __construct()
    {
    }

    public static function createFromConfiguration(Configuration $configuration, ObjectKey $objectKey)
    {
        $key = $objectKey->getKey();

        $instance = (new static)
            ->setResourcePath($configuration->getClassMetadataResource($key))
            ->setResourceType($configuration->getClassMetadataResourceType($key))
            ->setResourceClass($objectKey->getClass())
        ;

        $resourceMethod = $configuration->getClassMetadataProviderMethod($key);
        if (isset($resourceMethod)) {
            $instance->setResourceMethod($resourceMethod);
        }

        return $instance;
    }

    public static function create($resourcePath, $resourceType, $resourceClass, $resourceMethod)
    {
        return (new static)
            ->setResourcePath($resourcePath)
            ->setResourceType($resourceType)
            ->setResourceClass($resourceClass)
            ->setResourceMethod($resourceMethod)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getResourcePath()
    {
        return $this->resourcePath;
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceType()
    {
        return $this->resourceType;
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceClass()
    {
        return $this->resourceClass;
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceMethod()
    {
        return $this->resourceMethod;
    }

    /**
     * {@inheritdoc}
     */
    public function setResourcePath($resourcePath)
    {
        $this->resourcePath = $resourcePath;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setResourceType($resourceType)
    {
        $this->resourceType = $resourceType;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setResourceClass($resourceClass)
    {
        $this->resourceClass = $resourceClass;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setResourceMethod($resourceMethod)
    {
        $this->resourceMethod = $resourceMethod;

        return $this;
    }
}
