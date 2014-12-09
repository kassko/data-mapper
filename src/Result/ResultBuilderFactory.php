<?php

namespace Kassko\DataMapper\Result;

use Kassko\DataMapper\ObjectManager;

/**
 * Factory for ResultBuilder.
 *
 * @author kko
 */
class ResultBuilderFactory implements ResultBuilderFactoryInterface
{
    protected $objectManager;

    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * {@inheritdoc}
     */
    public function create($objectClass, $data = null)
    {
        return new ResultBuilder($this->objectManager, $objectClass, $data);
    }
}
