<?php

namespace Kassko\DataAccess\Result;

use Kassko\DataAccess\ObjectManager;

/**
 * Factory for ResultBuilder.
 *
 * @author kko
 */
class ResultBuilderFactory implements ResultBuilderFactoryInterface
{
    protected $objectManager;
    protected $data;

    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * {@inheritdoc}
     */
    public function createResultBuilder($objectClass, $data = null)
    {
        return new ResultBuilder($this->objectManager, $objectClass, $data);
    }
}
