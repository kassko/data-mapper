<?php

namespace Kassko\DataMapperTest;

/**
 * Class ObjectManagerTest
 * 
 * @author kko
 */
class ObjectManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function getCacheKeyValidateResult()
    {
        $this->markTestIncomplete('This test should be implement.');
    }

    /**
     * @test
     * @dataProvider argumentProvider
     */
    public function normalizeValueValidateResult(array $arguments)
    {
        $this->markTestIncomplete('This test should be implement.');
    }

    public function argumentProvider()
    {
        $fooObject = $this->getMockBuilder('fooClass')->getMock();

        return [
            [['foo', 'bar']],//Two scalar arguments
            [[['bar', 'baz']]],//One array argument 
            [[$this->getMockBuilder('fooClass')->getMock()]],//One object argument
            //Add one loadable object argument (a persistant object which uses LoadableTrait)
        ];
    }
}
