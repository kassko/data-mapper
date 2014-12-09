<?php

namespace Kassko\DataMapper\ClassMetadataLoader;

/**
 * Class metadata loader for files in yaml format.
 *
 * @author kko
 */
class YamlFileLoader extends YamlLoader
{
    public function supports(LoadingCriteriaInterface $loadingCriteria)
    {
        return 'yaml_file' === $loadingCriteria->getResourceType();
    }

    protected function doGetData(LoadingCriteriaInterface $loadingCriteria)
    {
        return $this->parseResourceFile($loadingCriteria->getResourcePath());
    }

    private function parseResourceFile($resource)
    {
        return $this->parseContent(file_get_contents($resource));
    }
}