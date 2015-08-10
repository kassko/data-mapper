<?php
namespace Kassko\DataMapperTest\Hydrator\Fixture\DataSource;

use Kassko\DataMapperTest\Hydrator\Fixture\Model\DataSourceParameters;

class ParametersDataSource
{
    public function getData(DataSourceParameters $object, array $data, $propertyValue, $valueInt, $valueString)
    {
        return '123';
    }
}
