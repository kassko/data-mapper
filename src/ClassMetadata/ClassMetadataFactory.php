<?php

namespace Kassko\DataMapper\ClassMetadata;

use Kassko\DataMapper\Annotation as OM;
use Kassko\DataMapper\Cache\CacheInterface;
use Kassko\DataMapper\ClassMetadataLoader\LoaderInterface as ClassMetadataLoaderInterface;
use Kassko\DataMapper\ClassMetadataLoader\LoadingCriteriaInterface;
use Kassko\DataMapper\Configuration\Configuration;
use Kassko\DataMapper\Configuration\ObjectKey;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
    private $eventManager;

    public function loadMetadata(ObjectKey $objectKey, LoadingCriteriaInterface $loadingCriteria, Configuration $configuration)
    {
        $cacheKey = $objectKey->getKey();

        if (! isset($this->loadedMetadata[$cacheKey])) {

            if ($this->cache->contains($cacheKey)) {
                $this->loadedMetadata[$cacheKey] = $this->cache->fetch($cacheKey);
            } else {
                $classMetadata = new ClassMetadata($objectKey->getClass());
                $this->metadataLoader->loadClassMetadata($classMetadata, $loadingCriteria, $configuration);
                $classMetadata->compile();

                $this->loadedMetadata[$cacheKey] = $classMetadata;
                $this->cache->save($cacheKey, $classMetadata);

                if ($this->eventManager) {
                    $this->eventManager->dispatch(Events::POST_LOAD_METADATA, new ClassMetadataEvent(new ClassMetadataBuilder($classMetadata)));
                }
            }
        }

        return $this->loadedMetadata[$cacheKey];
    }

    public function setClassMetadataLoader(ClassMetadataLoaderInterface $metadataLoader)
    {
        $this->metadataLoader = $metadataLoader;
        return $this;
    }

    public function setEventManager(EventDispatcherInterface $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    public function setCache(CacheInterface $cache)
    {
        $this->cache = $cache;
        return $this;
    }
}
