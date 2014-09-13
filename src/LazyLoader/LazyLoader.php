<?php

namespace Kassko\DataAccess\LazyLoader;

use Kassko\DataAccess\ObjectManager;

/**
 * Lazy load object properties.
 *
 * @author kko
 */
class LazyLoader
{
    private $objectManager;
    private $objectClass;
    private $instances = [];

    public function __construct(ObjectManager $objectManager, $objectClass)
    {
        $this->objectManager = $objectManager;
        $this->objectClass = $objectClass;
    }

    /**
     * Load an object property.
     * Property can be loaded only when needed for performance reason.
     *
     * @param array $object The object for wich we have to load property
     * @param array $propertyName The property to load
     */
    public function loadProperty($object, $propertyName)
    {
        if (get_class($object) !== $this->objectClass) {
            throw new \LogicException(sprintf("L'objet n'est pas du type attendu. Valeur '%s' attendue. Valeur '%s' obtenue.", $this->objectClass, get_class($object)));
        }

        $hydrator = $this->objectManager->getHydratorFor($this->objectClass);
        $hydrator->loadProperty($object, $propertyName);
    }
}
