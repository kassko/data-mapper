<?php

namespace Kassko\DataAccess\Query;

use Kassko\DataAccess\Listener\Events;
use Kassko\DataAccess\Listener\QueryEvent;
use Kassko\DataAccess\ObjectManager;

/**
 * Wrapp a query to do do related works.
 *
 * @author kko
 */
class Query
{
    private $objectManager;
    private $objectClass;
    private $extra = [];
    private $resultCacheParam;
    private $resultManager;

    public function __construct(ObjectManager $objectManager, $objectClass)
    {
        $this->objectManager = $objectManager;
        $this->objectClass = $objectClass;

        $this->resultManager = $objectManager->getResultManager();
        $objectManager->registerEvents($objectClass);
    }

    public function executeCreate($object, Callable $callback)
    {
        $this->dispatchEvent(Events::OBJECT_PRE_CREATE, $object);
        $result = $this->resultManager->create($object, $callback, $this->objectClass, $this->resultCacheParam);
        $this->dispatchEvent(Events::OBJECT_POST_CREATE, $result);

        return $result;
    }

    public function executeFind($id, Callable $callback)
    {
        $result = $this->resultManager->find($id, $callback, $this->objectClass, $this->resultCacheParam);
        $this->dispatchEvent(Events::OBJECT_POST_LOAD, $result);

        return $result;
    }

    public function executeFindBy(Callable $callback)
    {
        $result = $this->resultManager->findBy($callback, $this->objectClass, $this->resultCacheParam);
        $this->dispatchEvent(Events::OBJECT_POST_LOAD_LIST, $result);

        return $result;
    }

    public function executeUpdate($object, Callable $callback, Callable $findCallback = null)
    {
        $this->dispatchEvent(Events::OBJECT_PRE_UPDATE, $object);
        $result = $this->resultManager->update($object, $callback, $this->objectClass, $findCallback, $this->resultCacheParam);
        $this->dispatchEvent(Events::OBJECT_POST_UPDATE, $result);

        return $result;
    }

    public function executeDelete($object, Callable $callback)
    {
        $this->dispatchEvent(Events::OBJECT_PRE_DELETE, $object);
        $result = $this->resultManager->delete($object, $callback, $this->objectClass, $this->resultCacheParam);
        $this->dispatchEvent(Events::OBJECT_POST_DELETE, $result);

        return $result;
    }

    public function useCache()
    {
        $this->resultCacheParam =
            new CacheParam($this->objectManager->getConfiguration(), $this);

        return $this->resultCacheParam;
    }

    public function getExtra()
    {
        return $this->extra;
    }

    public function setExtra(array $extra)
    {
        $this->extra = $extra;
        return $this;
    }

    /**
     * Permet de récupérer les paramètres à transmettre à une connection au format attendu avec les paramètres nommés.
     *
     * @return array Renvoi les paramètres à transmettre à une connection au format attendu avec les paramètres nommés.
     */
    /*
    public function getNamedParam(array $paramNames, array $data)
    {
        $namedParam = [];

        foreach ($paramNames as $paramName) {
            $namedParam[$paramName] = $data[$paramName];
        }

        return $namedParam;
    }
    */

    /**
     * Diffuse un évènement pour les observeurs d'une entité.
     *
     * @param object|array $result Le résultat de la requête (entité ou collection d'entités) à diffuser avec l'évènement.
     *
     * @return QueryEvent Renvoi un objet évènement nourrit de données pour les observeurs (listeners).
     */
    private function dispatchEvent($eventName, $result)
    {
        $this->objectManager->dispatchEvent(
            $this->objectClass,
            $eventName,
            function () use ($result) {
                return $this->createEvent($result);
            }
        );
    }

    /**
     * Crée un objet évènement et le nourrit de données pour les observeurs d'une entité.
     *
     * @param object|array $result Le résultat de la requête (entité ou collection d'entités) à ajouter comme donnée à l'objet évènement.
     * @param array $param Les paramètres de la requêtes à ajouter comme donnée à l'objet évènement.
     *
     * @return QueryEvent Renvoi un objet évènement nourrit de données pour les observeurs (listeners).
     */
    private function createEvent($result)
    {
        return new QueryEvent($result, $this->extra);
    }
}
