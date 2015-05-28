<?php
namespace Kassko\DataMapperTest\ClassMetadata;

use Kassko\DataMapper\ClassMetadata;

/**
 * Class SourcePropertyMetadataTest
 * @package Kassko\DataMapperTest\ClassMetadata
 * @author Alexey Rusnak
 */
class SourcePropertyMetadataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ClassMetadata\SourcePropertyMetadata
     */
    protected $sourcePropertyMetadata;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->sourcePropertyMetadata = new ClassMetadata\SourcePropertyMetadata();
    }

    /**
     * @test
     * @dataProvider validateDefaultValuesDataProvider
     * @param string $field
     * @param string $value
     */
    public function validateDefaultValues($field, $value)
    {
        $this->assertSame($value, $this->sourcePropertyMetadata->$field);
    }

    /**
     * @test
     */
    public function hasDependsValidateResult()
    {
        $this->sourcePropertyMetadata->depends = array(true);

        $this->assertTrue($this->sourcePropertyMetadata->hasDepends());

        $this->sourcePropertyMetadata->depends = array();

        $this->assertFalse($this->sourcePropertyMetadata->hasDepends());
    }

    /**
     * @test
     * @dataProvider areDataInvalidValidateResultDataProvider
     * @param string $badReturnValue
     * @param mixed $data
     * @param mixed $expectedResult
     */
    public function areDataInvalidValidateResult($badReturnValue, $data, $expectedResult)
    {
        $this->sourcePropertyMetadata->badReturnValue = $badReturnValue;

        $this->assertSame($expectedResult, $this->sourcePropertyMetadata->areDataInvalid($data));
    }

    /**
     * @TODO: Fix source code
     * @expectedException \DomainException
     */
    public function areDataInvalidValidateDomainException()
    {
        $this->sourcePropertyMetadata->badReturnValue = 'invalidBadReturnValue';
        $this->sourcePropertyMetadata->areDataInvalid('testData');
    }

    /**
     * @return array
     */
    public function areDataInvalidValidateResultDataProvider()
    {
        return array(
            array(ClassMetadata\SourcePropertyMetadata::BAD_RETURN_VALUE_NULL, null, true),
            array(ClassMetadata\SourcePropertyMetadata::BAD_RETURN_VALUE_NULL, array(), false),
            array(ClassMetadata\SourcePropertyMetadata::BAD_RETURN_VALUE_FALSE, false, true),
            array(ClassMetadata\SourcePropertyMetadata::BAD_RETURN_VALUE_FALSE, true, false),
            array(ClassMetadata\SourcePropertyMetadata::BAD_RETURN_VALUE_EMPTY_STRING, 'test', false),
            array(ClassMetadata\SourcePropertyMetadata::BAD_RETURN_VALUE_EMPTY_STRING, '', true),
            array(ClassMetadata\SourcePropertyMetadata::BAD_RETURN_VALUE_EMPTY_ARRAY, array(), true),
            array(ClassMetadata\SourcePropertyMetadata::BAD_RETURN_VALUE_EMPTY_ARRAY, array('test'), false)
        );
    }

    /**
     * @return array
     */
    public function validateDefaultValuesDataProvider()
    {
        return array(
            array('id', null),
            array('class', null),
            array('method', null),
            array('args', null),
            array('lazyLoading', null),
            array('supplySeveralFields', null),
            array('onFail', null),
            array('exceptionClass', null),
            array('badReturnValue', null),
            array('fallbackSourceId', null),
            array('depends', array()),
            array('preprocessors', array()),
            array('processors', array())
        );
    }
}
