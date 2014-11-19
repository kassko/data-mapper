<?php

namespace Kassko\DataAccess\ClassMetadata;

use Symfony\Component\EventDispatcher\GenericEvent;

/**
* Contains class metadata events.
*
* @author kko
*/
class ClassMetadataEvent extends GenericEvent
{
    /**
     * @var ClassMetadataBuilder
     */
    protected $classMetadataBuilder;

    public function __construct(ClassMetadataBuilder $classMetadataBuilder, array $arguments = [])
    {
        parent::__construct($classMetadataBuilder, $arguments);

        $this->classMetadataBuilder = $classMetadataBuilder;
    }

    public function getClassMetadataBuilder()
    {
        return $this->classMetadataBuilder;
    }
}
