<?php

namespace Kassko\DataMapper\ClassMetadataLoader;

use Kassko\DataMapper\ClassMetadata\ClassMetadata;

/**
 * Find the good clas metadata loader.
 *
 * @author kko
 */
class LoaderResolver implements LoaderResolverInterface
{
    private $loaders;

    public function __construct(array $loaders = [])
    {
        $this->setLoaders($loaders);
    }

    public function resolveLoader(LoadingCriteriaInterface $loadingCriteria)
    {
        foreach ($this->loaders as $loader) {

            if ($loader->supports($loadingCriteria)) {

                return $loader;
            }
        }

        return false;
    }

    public function getLoaders()
    {
        return $this->loaders;
    }

    public function setLoaders(array $loaders)
    {
        $this->loaders = $loaders;

        return $this;
    }

    public function addLoader(LoaderInterface $loader)
    {
        $this->loaders[] = $loader;

        return $this;
    }

    public function addLoaders(array $loaders, $prepend = false)
    {
        if (false === $prepend) {
            $this->loaders += $loaders;
        } else {
            $this->loaders = $loaders + $this->loaders;
        }

        return $this;
    }

}