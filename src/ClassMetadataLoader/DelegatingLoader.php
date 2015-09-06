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
class DelegatingLoader implements LoaderInterface
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
        DelegatingLoader $delegatingLoader = null
    ) {
        $delegatedLoader = $this->getDelegatedLoader($loadingCriteria);
        return $delegatedLoader->loadClassMetadata($classMetadata, $loadingCriteria, $configuration, $this);
    }

    public function supports(LoadingCriteriaInterface $loadingCriteria)
    {
        return false !== $this->resolver->resolveLoader($loadingCriteria);
    }

    public function getDelegatedLoader(LoadingCriteriaInterface $loadingCriteria)
    {
        if (false === $delegatedLoader = $this->resolver->resolveLoader($loadingCriteria)) {
            throw new NotFoundLoaderException($loadingCriteria);
        }  

        return $delegatedLoader;
    }
}
