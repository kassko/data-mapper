<?php

namespace Kassko\DataMapper\ClassMetadata;

/**
 * Facilitate to modify metadata.
 *
 * TODO: to complete
 *
 * @author kko
 */
class ClassMetadataBuilder
{
    /**
     * @var ClassMetadata
     */
    protected $classMetadata;

    public function __construct(ClassMetadata $classMetadata)
    {
        $this->classMetadata = $classMetadata;
    }

    public function getClassMetadata()
    {
        return $this->classMetadata;
    }
}
