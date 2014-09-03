<?php

namespace Kassko\DataAccess\Result;

use Kassko\DataAccess\Hydrator\AbstractHydrator;
use Kassko\DataAccess\ObjectManager;

/**
 * Base for results hydration.
 *
 * @author kko
 */
abstract class AbstractResultHydrator
{
    protected $objectManager;

    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create an hydrate an entity from a raw results.
     *
     * @param array $data Raw results.
     *
     * @return array Return an hydrated object.
     */
    protected function hydrateItem($objectClass, array $item)
    {
        $object = $this->createObjectToHydrate($objectClass);

        if (0 == count($item)) {
            return $object;
        }

        $hydrator = $this->objectManager->getHydratorFor($objectClass);

        return $hydrator->hydrate($item, $object);
    }

    protected function createObjectToHydrate($objectClass)
    {
        return new $objectClass;
    }
}
