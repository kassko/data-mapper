<?php

namespace Kassko\DataMapper\ClassMetadataLoader;

/**
 * Class metadata loader for files in php format.
 *
 * @author kko
 */
class PhpFileLoader extends InnerPhpLoader
{
    public function supports(LoadingCriteriaInterface $loadingCriteria)
    {
        return 'php_file' === $loadingCriteria->getResourceType();
    }

    protected function doGetData(LoadingCriteriaInterface $loadingCriteria)
    {
        $resource = $loadingCriteria->getResourcePath();
        return require_once $resource;
    }
}