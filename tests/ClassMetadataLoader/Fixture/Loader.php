<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture;

use Kassko\DataMapper\ClassMetadata\ClassMetadata;
use Kassko\DataMapper\ClassMetadataLoader;
use Kassko\DataMapper\ClassMetadataLoader\LoadingCriteriaInterface;

class Loader extends ClassMetadataLoader\AbstractLoader
{
    function supports(LoadingCriteriaInterface $loadingCriteria)
    {
        throw new \RuntimeException('Method does not implemented.');
    }

    protected function doGetData(LoadingCriteriaInterface $loadingCriteria)
    {
    }

    protected function doLoadClassMetadata(ClassMetadata $classMetadata, array $data)
    {
    }
}
