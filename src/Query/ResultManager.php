<?php

namespace Kassko\DataAccess\Query;

use Kassko\DataAccess\ObjectManager;
use Kassko\DataAccess\Query\CacheParam;
use Kassko\DataAccess\Query\Exception\OptimisticLockException;

/**
* Stores results.
*
* @author kko
*/
class ResultManager
{
    private $identityMap = [];
    private $objectHashToObjectIdMap = [];
    private $objectManager;

    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(
        $object,
        Callable $callback,
        $objectClass,
        CacheParam $cacheParam = null
    ) {

        return call_user_func_array($callback, [$objectClass]);
    }

    public function find(
        $id,
        Callable $callback,
        $objectClass,
        CacheParam $cacheParam = null
    ) {
        $hashedId = $this->getHashedIdFromId($id);

        //Try to get entity from the identity map.
        if (isset($this->identityMap[$objectClass][$hashedId])) {
            return $this->identityMap[$objectClass][$hashedId];
        }

        //Try to get the entity in cache.
        if (null !== $cacheParam) {

            $objectCacheKey = $this->getCacheKey($hashedId, $objectClass);
            $cache = $cacheParam->getCache();

            if ($cache->contains($objectCacheKey)){
                return $this->identityMap[$objectClass][$hashedId] = $cache->fetch($objectCacheKey);
            }

            $data = $this->identityMap[$objectClass][$hashedId] = call_user_func_array($callback, [$objectClass]);
            $cache->save($objectCacheKey, $data, $cacheParam->getLifeTime());

            return $data;
        }

        //Get the entity from the storage and update the identity map.
        return $this->identityMap[$objectClass][$hashedId] = call_user_func_array($callback, [$objectClass]);
    }

    public function findBy(
        Callable $callback,
        $objectClass,
        CacheParam $cacheParam = null
    ) {
        if (null === $cacheParam) {
            return call_user_func_array($callback, [$objectClass]);
        }

        if (null === $collectionCacheKey = $cacheParam->getKey()) {
            throw new \LogicException('Une clé de cache doit être spécifiée pour les requêtes de lecture non CRUD.');
        }

        //Append the FQCN to the key to be sure its unique.
        $collectionCacheKey = $objectClass.$collectionCacheKey;

        $cache = $cacheParam->getCache();
        if ($cache->contains($collectionCacheKey)) {

            //Get from query cache the id's of object in the collection.
            $idCollection = $cache->fetch($collectionCacheKey);
            $collection = [];

            foreach ($idCollection as $id) {

                $hashedId = $this->getHashedIdFromId($id);

                //We do not want severals instances of entity for a same identity
                //so we return the existing in memory instance.
                if (isset($this->identityMap[$objectClass][$hashedId])) {

                    $object = $this->identityMap[$objectClass][$hashedId];
                } else {

                    //Get the object from the object cache and update the identity map.
                    $objectCacheKey = $this->getCacheKey($hashedId, $objectClass);
                    if ($cache->contains($objectCacheKey)) {

                         $object =
                         $this->identityMap[$objectClass][$hashedId] =
                         $cache->fetch($objectCacheKey);
                    } else {

                        //An object of the collection is not in the object cache anymore,
                        //so data of the query cache are not consistent anymore,
                        //then we read the storage and we update the cache with the collection.
                        return $this->cacheFindBy($callback, $objectClass, $cache, $collectionCacheKey, $cacheParam->getLifeTime());
                    }
                }

                $collection[] = $object;
            }

            return $collection;
        }

        return $this->cacheFindBy($callback, $objectClass, $cache, $collectionCacheKey, $cacheParam->getLifeTime());
    }

    public function update(
        $object,
        Callable $callback,
        $objectClass,
        Callable $findCallback = null,
        CacheParam $cacheParam = null
    ) {
        $result = call_user_func_array($callback, [$objectClass]);

        $this->checkOptimisticConcurrency($object, $findCallback, $cacheParam);

        if (null === $cacheParam) {
            return $result;
        }

        //Entity has changed, we remove it from the object cache.
        $this->detachObject($object, $cacheParam);

        if (null === $findCallback) {
            return $result;
        }

        //We want a fresh object (with changes).
        //Because it had been removed from object cache, it will be refresh from the storage.
        return call_user_func_array($findCallback, [$id]);
    }

    public function delete(
        $object,
        Callable $callback,
        $objectClass,
        CacheParam $cacheParam = null
    ) {
        $result = call_user_func_array($callback, [$objectClass]);

        //Entity has been removed, we remove it from the object cache.
        $this->detachObject($object, $cacheParam);

        return $result;
    }

    private function detachObject($object, CacheParam $cacheParam = null)
    {
        $objectClass = get_class($object);
        $hashedId = $this->getHashedIdFromObject($object);
        unset($this->identityMap[$objectClass][$hashedId]);
        unset($this->objectHashToObjectIdMap[$hashedId]);

        if (null !== $cacheParam) {

            $cache = $cacheParam->getCache();
            $objectCacheKey = $this->getCacheKey($hashedId, $objectClass);
            $cache->delete($objectCacheKey);
        }
    }

    private function getCacheKey($hashedId, $objectClass)
    {
        return $hashedId.$objectClass;
    }

    private function getHashedIdFromObject($object)
    {
        return $this->getHashedIdFromId($this->getId($object));
    }

    private function getHashedIdFromId($id)
    {
        return implode(' ', (array) $id);
    }

    private function getId($object)
    {
        $objectHash = spl_object_hash($object);

        if (isset($this->objectHashToObjectIdMap[$objectHash])) {
            return $this->objectHashToObjectIdMap[$objectHash];
        }

        $metadata = $this->objectManager->getMetadata($objectClass = get_class($object));
        $hydrator = $this->objectManager->createHydratorFor($objectClass);

        if ($metadata->hasId()) {
            $this->objectHashToObjectIdMap[$objectHash] = $metadata->extractId($object, $hydrator);
        } elseif ($metadata->hasIdComposite()) {
            $this->objectHashToObjectIdMap[$objectHash] = $metadata->extractIdComposite($object, $hydrator);
        } else {
            $this->objectHashToObjectIdMap[$objectHash] = $metadata->extractField($object, 'id', $hydrator);
        }

        return $this->objectHashToObjectIdMap[$objectHash];
    }

    private function getVersion($object)
    {
        $metadata = $this->objectManager->getMetadata($objectClass = get_class($object));

        if ($metadata->isVersionned()) {

            $hydrator = $this->objectManager->createHydratorFor($objectClass);
            return $metadata->extractVersion($object, $hydrator);
        }

        return null;
    }

    private function cacheFindBy(Callable $callback, $objectClass, $cache, $collectionCacheKey, $lifeTime)
    {
        //Fetch the object collection from storage.
        $collection = call_user_func_array($callback, [$objectClass]);

        $collectionToCache = $this->normalizeResultSetForCaching($collection);

        //Cache the fetched collection, only its object ids.
        $collectionId = array_map(
            function ($object) {

                return $this->getId($object);
            },
            $collectionToCache
        );

        $cache->save($collectionCacheKey, $collectionId, $lifeTime);

        return $collection;
    }

    private function normalizeResultSetForCaching($objectOrCollection)
    {
        if (! is_array($objectOrCollection)) {

            return [$objectOrCollection];
        }

        return $objectOrCollection;
    }


    private function checkOptimisticConcurrency($object, Callable $findCallback = null, CacheParam $cacheParam = null)
    {
        if (null !== $version = $this->getVersion($object)) {

            $idRef = $this->getId($object);
            $hashedId = $this->getHashedIdFromId($idRef);
            $objectCacheKey = $this->getCacheKey($hashedId, get_class($object));

            //If the cache is shared, fetch the ref object from it.
            if ($cacheParam->isShared() && $cache = $cacheParam->getCache() && $cache->contains($objectCacheKey)) {

                $objectRef = $cache->fetch($objectCacheKey);
            } elseif (null !== $findCallback) {//Otherwise fetch the ref object from the storage.

                $objectRef = call_user_func_array($findCallback, [$idRef]);
            }

            $versionRef = $this->getVersion($objectRef);
            if ($version !== $versionRef = $this->getVersion($objectRef)) {
                throw OptimisticLockException::versionMismatch($object, $versionRef, $version);
            }
        }
    }

}