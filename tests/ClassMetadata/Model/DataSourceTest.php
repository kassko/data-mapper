<?php
namespace Kassko\DataMapperTest\ClassMetadata;

use Kassko\DataMapper\ClassMetadata;

/**
 * Class DataSourceTest
 * 
 * @author Alexey Rusnak
 */
class DataSourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ClassMetadata\Model\DataSource
     */
    protected $dataSource;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->dataSource = new ClassMetadata\Model\DataSource();
    }

    /**
     * @test
     * @dataProvider validateDefaultValuesDataProvider
     * @param string $field
     * @param string $value
     */
    public function validateDefaultValues($field, $value)
    {
        $this->assertSame($value, $this->dataSource->$field());
    }

    /**
     * @test
     */
    public function hasDependsValidateResult()
    {
        $this->dataSource->setDepends(array(true));

        $this->assertTrue($this->dataSource->hasDepends());

        $this->dataSource->setDepends(array());

        $this->assertFalse($this->dataSource->hasDepends());
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
        $this->dataSource->setBadReturnValue($badReturnValue);

        $this->assertSame($expectedResult, $this->dataSource->areDataInvalid($data));
    }

    /**
     * @test
     * @expectedException \DomainException
     */
    public function areDataInvalidValidateDomainException()
    {
        $this->dataSource->setBadReturnValue('invalidBadReturnValue');
        $this->dataSource->areDataInvalid('testData');
    }

    /**
     * @return array
     */
    public function areDataInvalidValidateResultDataProvider()
    {
        return array(
            array(ClassMetadata\Model\DataSource::BAD_RETURN_VALUE_NULL, null, true),
            array(ClassMetadata\Model\DataSource::BAD_RETURN_VALUE_NULL, array(), false),
            array(ClassMetadata\Model\DataSource::BAD_RETURN_VALUE_FALSE, false, true),
            array(ClassMetadata\Model\DataSource::BAD_RETURN_VALUE_FALSE, true, false),
            array(ClassMetadata\Model\DataSource::BAD_RETURN_VALUE_EMPTY_STRING, 'test', false),
            array(ClassMetadata\Model\DataSource::BAD_RETURN_VALUE_EMPTY_STRING, '', true),
            array(ClassMetadata\Model\DataSource::BAD_RETURN_VALUE_EMPTY_ARRAY, array(), true),
            array(ClassMetadata\Model\DataSource::BAD_RETURN_VALUE_EMPTY_ARRAY, array('test'), false)
        );
    }

    /**
     * @return array
     */
    public function validateDefaultValuesDataProvider()
    {
        return array(
            array('getId', null),
            array('getMethod', null),
            array('getLazyLoading', null),
            array('getSupplySeveralFields', null),
            array('getOnFail', null),
            array('getExceptionClass', null),
            array('getBadReturnValue', null),
            array('getFallbackSourceId', null),
            array('getDepends', array()),
            array('getPreprocessors', array()),
            array('getProcessors', array())
        );
    }
}
