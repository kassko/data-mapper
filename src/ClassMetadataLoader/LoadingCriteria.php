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

        $instance = (new self)
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
        return (new self)
            ->setResourcePath($resourcePath)
            ->setResourceType($resourceType)
            ->setResourceClass($resourceClass)
            ->setResourceMethod($resourceMethod)
        ;
    }

    /**
     * Gets the value of resourcePath.
     *
     * @return string the resource path
     */
    public function getResourcePath()
    {
        return $this->resourcePath;
    }

    /**
     * Gets the value of resourceType.
     *
     * @return string the resource type
     */
    public function getResourceType()
    {
        return $this->resourceType;
    }

    /**
     * Gets the value of resourceClass.
     *
     * @return string the resource class
     */
    public function getResourceClass()
    {
        return $this->resourceClass;
    }

    /**
     * Gets the value of resourceMethod.
     *
     * @return string the resource method
     */
    public function getResourceMethod()
    {
        return $this->resourceMethod;
    }

    /**
     * Sets the value of resourcePath.
     *
     * @param string $resourcePath the resource path
     *
     * @return self
     */
    public function setResourcePath($resourcePath)
    {
        $this->resourcePath = $resourcePath;

        return $this;
    }

    /**
     * Sets the value of resourceType.
     *
     * @param string $resourceType the resource type
     *
     * @return self
     */
    public function setResourceType($resourceType)
    {
        $this->resourceType = $resourceType;

        return $this;
    }

    /**
     * Sets the value of resourceClass.
     *
     * @param string $resourceClass the resource class
     *
     * @return self
     */
    public function setResourceClass($resourceClass)
    {
        $this->resourceClass = $resourceClass;

        return $this;
    }

    /**
     * Sets the value of resourceMethod.
     *
     * @param string $resourceMethod the resource method
     *
     * @return self
     */
    public function setResourceMethod($resourceMethod)
    {
        $this->resourceMethod = $resourceMethod;

        return $this;
    }
}
