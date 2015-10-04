<?php
namespace Kassko\DataMapperTest\ClassMetadata;

use Kassko\DataMapper\ClassMetadata;

/**
 * Class MethodMetadataTest
 * 
 * @author Alexey Rusnak
 */
class MethodTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ClassMetadata\Method
     */
    protected $methodMetadata;

    /**
     * @test
     * @dataProvider constructorParamProvider
     */
    public function validateConstructor($className, $method, $arguments)
    {
        $methodMetadata = new ClassMetadata\Model\Method($className, $method, $arguments);

        $this->assertSame($className, $methodMetadata->getClass());
        $this->assertSame($method, $methodMetadata->getFunction());
        $this->assertSame($arguments, $methodMetadata->getArgs());
    }

    public function constructorParamProvider()
    {
        return array(
            array('Kassko\DataMapperTest\ClassMetadata\Fixture\SampleClass', 'method', array()),
        );
    }

    /**
     * @test
     * @dataProvider notNullCaseProvider
     */
    public function isNullValidateResultFalse($className, $method, $arguments)
    {
        $methodMetadata = new ClassMetadata\Model\Method($className, $method, $arguments);

        $this->assertFalse($methodMetadata->isNull());
    }

    public function notNullCaseProvider()
    {
        return array(
            array('Kassko\DataMapperTest\ClassMetadata\Fixture\SampleClass', 'method', array()),
        );
    }

    /**
     * @test
     * @dataProvider nullCaseProvider
     */
    public function isNullValidateResultTrue($className, $method, $arguments)
    {
        $methodMetadata = new ClassMetadata\Model\Method($className, $method, $arguments);

        $this->assertTrue($methodMetadata->isNull());
    }

    public function nullCaseProvider()
    {
        return array(
            array(null, null, array()),
            array('Kassko\DataMapperTest\ClassMetadata\Fixture\SampleClass', null, array()),
        );
    }
}
