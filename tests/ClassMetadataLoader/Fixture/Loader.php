<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture;

use Kassko\DataMapper\ClassMetadataLoader;
use Kassko\DataMapper\ClassMetadataLoader\LoadingCriteriaInterface;

class Loader extends ClassMetadataLoader\AbstractLoader
{
    function supports(LoadingCriteriaInterface $loadingCriteria)
    {
        throw new \RuntimeException('Method does not implemented.');
    }
}
