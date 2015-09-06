<?php

namespace Kassko\DataMapper\ClassMetadataLoader;

/**
 * Contains criteria to retrieve metadata and their location. 
 * 
 * Should be removed. The real implementation LoadingCriteria is enough.
 *
 * @author kko
 */
interface LoadingCriteriaInterface
{
	/**
     * Gets the value of resourcePath.
     *
     * @return string the resource path
     */
    public function getResourcePath();

    /**
     * Gets the value of resourceType.
     *
     * @return string the resource type
     */
    public function getResourceType();

    /**
     * Gets the value of resourceClass.
     *
     * @return string the resource class
     */
    public function getResourceClass();

    /**
     * Gets the value of resourceMethod.
     *
     * @return string the resource method
     */
    public function getResourceMethod();

    /**
     * Sets the value of resourcePath.
     *
     * @param string $resourcePath the resource path
     *
     * @return self
     */
    public function setResourcePath($resourcePath);

    /**
     * Sets the value of resourceType.
     *
     * @param string $resourceType the resource type
     *
     * @return self
     */
    public function setResourceType($resourceType);

    /**
     * Sets the value of resourceClass.
     *
     * @param string $resourceClass the resource class
     *
     * @return self
     */
    public function setResourceClass($resourceClass);

    /**
     * Sets the value of resourceMethod.
     *
     * @param string $resourceMethod the resource method
     *
     * @return self
     */
    public function setResourceMethod($resourceMethod);
}
