<?php

namespace Kassko\DataAccess\Result;

use Kassko\DataAccess\Result\Exception\DuplicatedIndexException;

/**
 * Hydrate results walkable with iterator.
 *
 * @author kko
 */
class IterableResultHydrator extends AbstractResultHydrator
{
    /**
     * Hydrate results.
     *
     * @param array $data Raw results.
     *
     * @return Generator
     */
    public function hydrate($objectClass, array $data, $indexOfBy = false)
    {
        $this->objectClass = $objectClass;

        if (0 == count($data)) {

            yield $this->createObjectToHydrate($this->objectClass);
        } elseif (! is_array(current($data))) {

            yield $this->hydrateItem($this->objectClass, $data);
        } elseif (false === $indexOfBy) {

            foreach ($data as $item) {
                yield $this->hydrateItem($this->objectClass, $item);
            }
        } else {

            foreach ($this->hydrateBy($data, $indexOfBy) as $valueOfBy => $item) {
                yield $valueOfBy => $item;
            }
        }
    }

    private function hydrateBy(array $data, $indexOfBy)
    {
        $metadata = $this->objectManager->getMetadata($this->objectClass);
        $originalFieldName = $metadata->getOriginalFieldName($indexOfBy);

        $valuesOfBy = [];
        foreach ($data as $key => $item) {

            $valueOfBy = $item[$originalFieldName];

            if (! isset($valueOfBy)) {
                continue;
            }

            $item = $this->hydrateItem($this->objectClass, $item);

            if (isset($valueOfByUsed[$valueOfBy])) {
                throw new DuplicatedIndexException($valueOfBy, $this->objectClass);
            }

            $valueOfByUsed[$valueOfBy] = true;

            yield $valueOfBy => $item;
        }
    }
}
