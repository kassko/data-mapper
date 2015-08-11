<?php
namespace Kassko\DataMapperTest\Hydrator\Fixture\DataSource;

use Kassko\DataMapperTest\Hydrator\Fixture\Model\DataSourcesParameters;

class ParametersDataSource
{
    public function getData(DataSourcesParameters $object, array $data, $propertyValue, $valueInt, $valueString)
    {
        return '123';
    }
}
