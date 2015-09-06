<?php

namespace Kassko\DataMapper\ClassMetadataLoader;

/**
 * Class metadata loader for php array data provided by objects.
 *
 * @author kko
 */
class InnerPhpLoader extends ArrayLoader
{
    public function supports(LoadingCriteriaInterface $loadingCriteria)
    {
        return
            'inner_php' === $loadingCriteria->getResourceType()
            &&
            method_exists($loadingCriteria->getResourceClass(), $loadingCriteria->getResourceMethod())
        ;
    }

    protected function doGetData(LoadingCriteriaInterface $loadingCriteria)
    {
        $callable = [$loadingCriteria->getResourceClass(), $loadingCriteria->getResourceMethod()];
        return $callable();
    }
}
