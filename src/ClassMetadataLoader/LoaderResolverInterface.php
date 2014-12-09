<?php

namespace Kassko\DataMapper\ClassMetadataLoader;

/**
 * Contract to find the good clas metadata loader.
 *
 * @author kko
 */
interface LoaderResolverInterface
{
    function resolveLoader(LoadingCriteriaInterface $loadingCriteria);
}