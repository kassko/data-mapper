<?php
namespace Kassko\DataMapperTest\Hydrator\Fixture\DataSource;

use Kassko\DataMapper\Annotation as DM;

class PersonDataSource
{
    public function getData()
    {
        return 'name';
    }

    public function getLazyLoadedData()
    {
        return 'address';
    }
}
