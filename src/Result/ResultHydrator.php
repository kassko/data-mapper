<?php

namespace Kassko\DataMapper\Result;

use Kassko\DataMapper\Result\Exception\DuplicatedIndexException;

/**
 * Hydrate results.
 *
 * @author kko
 */
class ResultHydrator extends AbstractResultHydrator
{
    private $objectClass;

    /**
     * Hydrate results.
     *
     * @param array $data Raw results.
     *
     * @return array Return an object collection even if none or only one result found.
     */
    public function hydrate($objectClass, array $data, $indexOfBy = false)
    {
        $this->objectClass = $objectClass;

        if (0 == count($data)) {

            return [];
        }

        if (! is_array(current($data))) {

            return $this->hydrateItem($this->objectClass, $data);
        }

        if (false === $indexOfBy) {

            foreach ($data as &$item) {
                $item = $this->hydrateItem($this->objectClass, $item);
            }
            unset($item);//<= par précaution.

            return $data;
        }

        return $this->hydrateBy($data, $indexOfBy);
    }

    private function hydrateBy(array $data, $indexOfBy)
    {
        $metadata = $this->objectManager->getMetadata($this->objectClass);
        $originalFieldName = $metadata->getOriginalFieldName($indexOfBy);

        $assocData = [];
        foreach ($data as $key => $item) {

            $valueOfBy = $item[$originalFieldName];

            if (! isset($valueOfBy)) {
                continue;
            }

            $item = $this->hydrateItem($this->objectClass, $item);

            if (isset($assocData[$valueOfBy])) {
                throw new DuplicatedIndexException($valueOfBy, $this->objectClass);
            }

            $assocData[$valueOfBy] = $item;
        }

        return $assocData;
    }
}
