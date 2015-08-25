<?php

namespace Kassko\DataMapper\ClassMetadataLoader;

use Symfony\Component\Yaml\Parser;

/**
 * Class metadata loader for yaml data provided by objects.
 *
 * @author kko
 */
class InnerYamlLoader extends ArrayLoader
{
    public function supports(LoadingCriteriaInterface $loadingCriteria)
    {
        return
            'inner_yaml' === $loadingCriteria->getResourceType()
            &&
            method_exists($loadingCriteria->getResourceClass(), $loadingCriteria->getResourceMethod())
        ;
    }

    protected function doGetData(LoadingCriteriaInterface $loadingCriteria)
    {
        $callable = [$loadingCriteria->getResourceClass(), $loadingCriteria->getResourceMethod()];
        return $this->parseContent($callable());
    }

    protected function parseContent($content)
    {
        return (new Parser())->parse($content);
    }
}