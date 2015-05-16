<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture;

use Kassko\DataMapper\ClassMetadataLoader;

class LoadingCriteria implements ClassMetadataLoader\LoadingCriteriaInterface
{
    public function getResourcePath(){}
    public function getResourceType(){}
    public function getResourceClass(){}
    public function getResourceMethod(){}
}