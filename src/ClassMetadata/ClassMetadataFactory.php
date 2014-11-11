<?php

namespace Kassko\DataAccess\ClassMetadata;

use Kassko\DataAccess\Annotation as OM;
use Kassko\DataAccess\Cache\CacheInterface;
use Kassko\DataAccess\ClassMetadataLoader\LoaderInterface as ClassMetadataLoaderInterface;
use Kassko\DataAccess\Configuration\ObjectKey;

/**
* Factory to create class metadata.
*
* @author kko
*/
class ClassMetadataFactory implements ClassMetadataFactoryInterface, ClassMetadataFactoryOptionsAwareInterface
{
	private $cache;
	private $metadataLoader;
	private $loadedMetadata = [];

	public function loadMetadata(ObjectKey $objectKey, $resourceName, $resourceType)
	{
        $cacheKey = $objectKey->getKey();

		if (! isset($this->loadedMetadata[$cacheKey])) {

			if ($this->cache->contains($cacheKey)) {
                $this->loadedMetadata[$cacheKey] = $this->cache->fetch($cacheKey);
            } else {
                $objectMetadata = new ClassMetadata($objectKey->getClass());
                $this->metadataLoader->loadClassMetadata($objectMetadata, $resourceName, $resourceType);
                $objectMetadata->compile();

                $this->loadedMetadata[$cacheKey] = $objectMetadata;
                $this->cache->save($cacheKey, $objectMetadata);
            }
		}

		return $this->loadedMetadata[$cacheKey];
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
}
