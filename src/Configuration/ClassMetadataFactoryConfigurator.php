<?php

namespace Kassko\DataMapper\Configuration;

use Kassko\DataMapper\ClassMetadata\ClassMetadataFactoryOptionsAwareInterface;

/**
 * Configure class metadata factory dependencies.
 *
 * @author kko
 */
class ClassMetadataFactoryConfigurator
{
    private $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function configure(ClassMetadataFactoryOptionsAwareInterface $classMetadataFactory)
    {
        $this->configuration->visitMetadataFactoryAndSetCache($classMetadataFactory);
    }
}
