<?php
namespace Kassko\DataMapperTest\Hydrator\Fixture\Model;

class CaseCollectionOfObjectsWithDataSourceArgsToResolve
{
    public $items = [];

    public function __construct()
    {
        $this->items[] = (new CaseDataSourceWithArgsToResolve)->setPropertyA('value a');
        $this->items[] = (new CaseDataSourceWithArgsToResolve)->setPropertyA('value b');
    }

    public function getItems()
    {
        return $this->items;
    }

    public function setItems($items)
    {
        $this->items = $items;

        return $this;
    }
}
