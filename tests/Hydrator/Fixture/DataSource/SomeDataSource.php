<?php
namespace Kassko\DataMapperTest\Hydrator\Fixture\DataSource;

class SomeDataSource
{
    public function getData()
    {
        return 'some data';
    }

    public function getFallbackData()
    {
        return 'some fallback data';
    }
}
