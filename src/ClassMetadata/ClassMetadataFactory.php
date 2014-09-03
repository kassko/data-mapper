<?php

namespace Kassko\DataAccess\ClassMetadata;

use Kassko\DataAccess\Annotation as OM;
use Kassko\DataAccess\Configuration\Configuration;
use Kassko\DataAccess\MappingDriver\AnnotationDriver;
use Kassko\DataAccess\ClassMetadataLoader\LoaderInterface as ClassMetadataLoaderInterface;
use Doctrine\Common\Cache\Cache as CacheInterface;

/**
* Factory to create class metadata.
*
* @author kko
*/
class ClassMetadataFactory implements
	ClassMetadataFactoryInterface,
	ClassMetadataFactoryOptionsAwareInterface
{
	private $cache;
	private $metadataLoader;
	private $loadedMetadata = [];
	private $driver;

	public function loadMetadata($className, Configuration $config)
	{
		if (! isset($this->loadedMetadata[$className])) {

			$this->doLoadMetadata($className, $config, null);
		}

		return $this->loadedMetadata[$className];
	}

	public function loadValueObjectMetadata($valueObjectClassName, $entityClassName, Configuration $config)
	{
		if (! isset($this->loadedMetadata[$valueObjectClassName])) {

			$this->doLoadMetadata($valueObjectClassName, $config, $entityClassName);
		}

		return $this->loadedMetadata[$valueObjectClassName];
	}

	public function setClassMetadataLoader(ClassMetadataLoaderInterface $metadataLoader)
	{
		$this->metadataLoader = $metadataLoader;
		return $this;
	}

	public function setCache(CacheInterface $cache)
	{
		$this->cache = $cache;
		return $this;
	}

	private function doLoadMetadata($className, Configuration $config)
	{
		//$cacheKey = md5($className);
		$cacheKey = $className;

		if ($this->cache->contains($cacheKey)) {
		//if (0) {

			$this->loadedMetadata[$className] = $this->cache->fetch($cacheKey);
    	} else {

    		$objectMetadata = new ClassMetadata($className);
    		$this->metadataLoader->loadObjectMetadata(
    			$objectMetadata,
    			$config->getClassMetadataResource($className),
    			$config->getClassMetadataResourceType($className)
    			);
    		//$this->doLoadValueObjectsMetadata($objectMetadata);
    		$objectMetadata->compile();

    		$this->loadedMetadata[$className] = $objectMetadata;
    		$this->cache->save($cacheKey, $objectMetadata);
    	}
	}

	/*
	private function doLoadValueObjectsMetadata(ClassMetadata $objectMetadata)
	{
		$valueObjectsClassNames = $objectMetadata->getValueObjectsClassNames();
    	foreach ($valueObjectsClassNames as $valueObjectsClassName) {

    		$valueObjectMetadata = new ClassMetadata($valueObjectsClassName);
			$this->metadataLoader->loadValueObjectMetadata($valueObjectMetadata);
			$objectMetadata->mergeValueObjectMetadata($valueObjectsClassName, $valueObjectMetadata);
    	}
	}
	*/
}