<?php

namespace Kassko\DataMapper\ClassMetadataLoader;

use Kassko\DataMapper\ClassMetadata\ClassMetadata;
use Kassko\DataMapper\Configuration\Configuration;

/**
 * Contract for class metadata loaders.
 *
 * @author kko
 */
interface LoaderInterface
{
    function loadClassMetadata(
        ClassMetadata $classMetadata,
        LoadingCriteriaInterface $loadingCriteria,
        Configuration $configuration,
        LoaderInterface $loader = null
    );
    function supports(LoadingCriteriaInterface $loadingCriteria);
}
