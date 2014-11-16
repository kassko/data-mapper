<?php

namespace Kassko\DataAccess\ClassMetadataLoader;

/**
 * Class metadata loader for files in php format.
 *
 * @author kko
 */
class PhpFileLoader extends PhpLoader
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