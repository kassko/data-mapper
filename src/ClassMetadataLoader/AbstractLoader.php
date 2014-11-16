<?php

namespace Kassko\DataAccess\ClassMetadataLoader;

use Kassko\DataAccess\ClassMetadata\ClassMetadata;
use Kassko\DataAccess\Configuration\Configuration;

abstract class AbstractLoader implements LoaderInterface
{
    public function loadClassMetadata(
        ClassMetadata $classMetadata,
        LoadingCriteriaInterface $loadingCriteria,
        Configuration $configuration,
        LoaderInterface $loader = null
    ) {
        $data = $this->getData($loadingCriteria, $configuration, $loader);
        return $this->doLoadClassMetadata($classMetadata, $data);
    }

    public function getData(
        LoadingCriteriaInterface $loadingCriteria,
        Configuration $configuration,
        LoaderInterface $loader
    ) {
        $data = $this->doGetData($loadingCriteria);

        $data = $this->importResource($data, $loadingCriteria, $loader, $configuration);
        $data = $this->importConfig($data, $loadingCriteria, $loader, $configuration);

        return $data;
    }

    protected function doGetData(LoadingCriteriaInterface $loadingCriteria)
    {//TODO: Normalize data after fetching them because the array of data obtained is different according to format yaml, php and the others.
        throw new \LogicException(sprintf('Not implemented function "%s::doGetData()"', static::class));
    }

    protected function doLoadClassMetadata(ClassMetadata $classMetadata, array $data)
    {
        throw new \LogicException(sprintf('Not implemented function "%s::doLoadClassMetadata()"', static::class));
    }

    private function importResource(
        array $data,
        LoadingCriteriaInterface $loadingCriteria,
        LoaderInterface $loader,
        Configuration $configuration
    ) {
        $defaultResourceDir = $configuration->getDefaultClassMetadataResourceDir();

        if (isset($data['imports']['resources'])) {

            foreach ($data['imports']['resources'] as $resourceSettings) {

                $otherResourcePath = null;
                if (isset($resourceSettings['path'])) {
                    $otherResourcePath = $resourceSettings['path'];
                    if ('.' === $otherResourceDir = dirname($otherResourcePath)) {
                        $otherResourcePath = $defaultResourceDir.'/'.$otherResourcePath;
                    }
                }

                $otherResourceType = null;
                if (isset($resourceSettings['type'])) {
                    $otherResourceType = $resourceSettings['type'];
                }

                $otherResourceClass = null;
                if (isset($resourceSettings['class'])) {
                    $otherResourceClass = $resourceSettings['class'];
                }

                $otherResourceMethod = null;
                if (isset($resourceSettings['method'])) {
                    $otherResourceMethod = $resourceSettings['method'];
                }

                $loadingCriteria = LoadingCriteria::create(
                    $otherResourcePath,
                    $otherResourceType,
                    $otherResourceClass,
                    $otherResourceMethod
                );

                $othersData = $loader->getData($loadingCriteria, $configuration, $loader);
                $data = array_merge_recursive($othersData, $data);
            }
        }

        return $data;
    }

    private function importConfig(
        array $data,
        LoadingCriteriaInterface $loadingCriteria,
        LoaderInterface $loader,
        Configuration $configuration
    ) {
        if (isset($data['imports']['config'])) {

            foreach ($data['imports']['config'] as $resourceSettings) {

                $objectClassConfig = null;
                if (isset($resourceSettings['class'])) {
                    $objectClassConfig = $resourceSettings['class'];
                }

                $loadingCriteria = LoadingCriteria::createFromConfiguration($configuration, $objectClassConfig);

                $othersData = $loader->getData($loadingCriteria, $configuration, $loader);
                $data = array_merge_recursive($othersData, $data);
            }
        }

        return $data;
    }
}