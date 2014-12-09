<?php

namespace Kassko\DataMapper\Result;

use Kassko\DataMapper\ObjectManager;

/**
 * Extract results from an object representation (object or collection).
 *
 * @author kko
 */
class ResultExtractor
{
    private $objectManager;

    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Transform to a result set an object representation (object or collection).
     *
     * @return array
     */
    public function extract($objectClass, $result)
    {
        $hydrator = $this->objectManager->getHydratorFor($objectClass);

        return $hydrator->extract($result);
    }
}
