<?php

namespace Kassko\DataAccess\ClassMetadataLoader;

use Kassko\DataAccess\ClassMetadata\ClassMetadata;
use Kassko\DataAccess\Configuration\Configuration;

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
    function getData(LoadingCriteriaInterface $loadingCriteria, Configuration $configuration, LoaderInterface $loader);
}