<?php
namespace Kassko\DataMapperTest\Integration\Fixture\Source;

class PersonSource
{
    public function getData()
    {
        return [
            'firstName' => 'Daniel',
            'lastName' => 'Jackson'
        ];
    }
}
