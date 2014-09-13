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

        //On essaie de récupèrer l'entité d'identité $id dans l'identity map'.
        if (isset($this->identityMap[$objectClass][$hashedId])) {
            return $this->identityMap[$objectClass][$hashedId];
        }

        //On essaie de récupèrer l'entité d'identité $id dans le cache.
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

        //echo 'query';

        //A défaut, on récupère l'entité d'identité $id dans le stockage.

        return $this->identityMap[$objectClass][$hashedId] = call_user_func_array($callback, [$objectClass]);
    }

    /*
    private function loadVersionInObjectFromRawResult($object, $rawResult)
    {
        $metadata = $this->objectManager->getMetadata($objectClass = get_class($object));
        $mappedVersionFieldName = $metadata->getMappedVersionFieldName();

        $hydrator = $this->objectManager->createHydratorFor($objectClass);
        $hydrator->hydrateProperty($object, $mappedVersionFieldName, $rawResult, 1);
    }
    */

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

        //On ajoute le FQCN à la clé fournie pour favoriser son unicité.
        $collectionCacheKey = $objectClass.$collectionCacheKey;

        $cache = $cacheParam->getCache();
        if ($cache->contains($collectionCacheKey)) {

            //On récupère du cache de requêtes les id des objets de la collection.
            $idCollection = $cache->fetch($collectionCacheKey);
            $collection = [];

            foreach ($idCollection as $id) {

                $hashedId = $this->getHashedIdFromId($id);

                //On ne veut pas avoir des instances différentes d'entités de même identité. On veut renvoyer l'éventuelle instance existante.
                if (isset($this->identityMap[$objectClass][$hashedId])) {

                    $object = $this->identityMap[$objectClass][$hashedId];
                } else {

                    //On récupère l'objet dans le cache des entités et on met-à-jour l'identity map.
                    $objectCacheKey = $this->getCacheKey($hashedId, $objectClass);
                    if ($cache->contains($objectCacheKey)) {

                         $object =
                         $this->identityMap[$objectClass][$hashedId] =
                         $cache->fetch($objectCacheKey);
                    } else {

                        //Un objet de la collection n'est plus dans le cache des entités.
                        //La cohérence des données du cache de requêtes n'est donc plus assurée,
                        //Alors on requête le stockage et on remet dans le cache de requêtes la collection d'identités.
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

        $this->checkOptimisticConcurrency($object, $findCallback);

        if (null === $cacheParam) {
            return $result;
        }

        //L'entité a changé, on la retire du cache des entités.
        $this->detachObject($object, $cacheParam);

        if (null === $findCallback) {
            return $result;
        }

        //On veut récupérer les modifications faîtes sur l'entité.
        //Rappelons que l'on vient de la supprimer du cache des entités, elle sera donc raffraichie à partir de la couche stockage.
        return call_user_func_array($findCallback, [$id]);
    }

    public function delete(
        $object,
        Callable $callback,
        $objectClass,
        CacheParam $cacheParam = null
    ) {
        $result = call_user_func_array($callback, [$objectClass]);

        //L'entité a été supprimé, on la retire du cache des entités.
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
        //A défaut, on récupère la collection du stockage.
        $collection = call_user_func_array($callback, [$objectClass]);

        $collectionToCache = $this->normalizeResultSetForCaching($collection);

        //Et on met en cache la collection où les entités sont représentés par leurs id.
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


    private function checkOptimisticConcurrency($object, Callable $findCallback = null)
    {
        if (null !== $findCallback && null !== $version = $this->getVersion($object)) {

            $idRef = $this->getId($object);
            $objectRef = call_user_func_array($findCallback, [$idRef]);

            $versionRef = $this->getVersion($objectRef);

            if ($version !== $versionRef = $this->getVersion($objectRef)) {

                throw OptimisticLockException::versionMismatch($object, $versionRef, $version);
            }
        }
    }

}