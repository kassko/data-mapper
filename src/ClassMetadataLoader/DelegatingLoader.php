<?php

namespace Kassko\DataMapper\ClassMetadataLoader;

use Kassko\DataMapper\ClassMetadataLoader\Exception\NotFoundLoaderException;
use Kassko\DataMapper\ClassMetadata\ClassMetadata;
use Kassko\DataMapper\Configuration\Configuration;

/**
 * Find the good class metadata loader and delegate to it class metadata loading.
 *
 * @author kko
 */
class DelegatingLoader extends AbstractLoader
{
    private $resolver;

    public function __construct(LoaderResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    public function loadClassMetadata(
        ClassMetadata $classMetadata,
        LoadingCriteriaInterface $loadingCriteria,
        Configuration $configuration,
        LoaderInterface $loader = null
    ) {
        if (false === $loader = $this->resolver->resolveLoader($loadingCriteria)) {
            throw new NotFoundLoaderException($loadingCriteria);
        }

        return $loader->loadClassMetadata($classMetadata, $loadingCriteria, $configuration, $this);
    }

    public function supports(LoadingCriteriaInterface $loadingCriteria)
    {
        return false !== $this->resolver->resolveLoader($loadingCriteria);
    }

    public function getData(
        LoadingCriteriaInterface $loadingCriteria,
        Configuration $configuration,
        LoaderInterface $loader
    ) {
        if (false === $loader = $this->resolver->resolveLoader($loadingCriteria)) {
            throw new NotFoundLoaderException($loadingCriteria);
        }

        return $loader->getData($loadingCriteria, $configuration, $loader);
    }
}
