<?php
namespace Kassko\DataMapperTest\Hydrator\Fixture\DataSource;

use Kassko\DataMapper\Annotation as DM;

class SupplySeveralFieldsDataSource
{
    public function getScalarData()
    {
        return 'value';
    }

    public function getArrayData()
    {
        return [
            'propertyItemValueA',
            'propertyItemValueB',
        ];
    }

    public function getScalarDataForSeveralFields()
    {
        return ['propertyC' => 'propertyItemValueA', 'propertyD' => 'propertyItemValueB'];
    }

    public function getArrayDataForSeveralFields()
    {
        return [
            'propertyE' => ['propertyAItemValueA', 'propertyAItemValueB'],
            'propertyF' => ['propertyBItemValueA', 'propertyBItemValueB'],
        ];
    }

    public function getObjectData()
    {
        return [
            'propertyA' => 'propertyItemValueA',
            'propertyB' => 'propertyItemValueB',
        ];
    }

    public function getObjectDataForSeveralFields()
    {
        return [
            'propertyH' => [
                'propertyA' => 'propertyItemValueA',
                'propertyB' => 'propertyItemValueB',
            ],
            'propertyI' => [
                'propertyA' => 'propertyItemValueA',
                'propertyB' => 'propertyItemValueB',
            ],
        ];
    }

    public function getArrayOfObjectData()
    {
        return [
            [
                'propertyA' => 'propertyItemValueA',
                'propertyB' => 'propertyItemValueB',
            ],
            [
                'propertyA' => 'propertyItemValueC',
                'propertyB' => 'propertyItemValueD',
            ],
        ];
    }

    public function getArrayOfObjectDataForSeveralFields()
    {
        return [
            'propertyK' => [
                [
                    'propertyA' => 'propertyItemValueA',
                    'propertyB' => 'propertyItemValueB',
                ],
                [
                    'propertyA' => 'propertyItemValueC',
                    'propertyB' => 'propertyItemValueD',
                ],
            ],
            'propertyL' => [
                [
                    'propertyA' => 'propertyItemValueE',
                    'propertyB' => 'propertyItemValueF',
                ],
                [
                    'propertyA' => 'propertyItemValueG',
                    'propertyB' => 'propertyItemValueH',
                ],
            ],
        ];
    }
}
