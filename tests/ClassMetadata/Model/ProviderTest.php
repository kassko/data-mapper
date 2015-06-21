<?php
namespace Kassko\DataMapperTest\ClassMetadata;

use Kassko\DataMapper\ClassMetadata;

/**
 * Class ProviderTest
 * 
 * @author kko
 */
class ProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ClassMetadata\Model\Provider
     */
    protected $provider;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->provider = new ClassMetadata\Model\Provider();
    }

    /**
     * @test
     * @dataProvider validateDefaultValuesDataProvider
     * @param string $field
     * @param string $value
     */
    public function validateDefaultValues($field, $value)
    {
        $this->assertSame($value, $this->provider->$field());
    }

    /**
     * @test
     */
    public function hasDependsValidateResult()
    {
        $this->provider->setDepends(array(true));

        $this->assertTrue($this->provider->hasDepends());

        $this->provider->setDepends(array());

        $this->assertFalse($this->provider->hasDepends());
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
        $this->provider->setBadReturnValue($badReturnValue);

        $this->assertSame($expectedResult, $this->provider->areDataInvalid($data));
    }

    /**
     * @test
     * @expectedException \DomainException
     */
    public function areDataInvalidValidateDomainException()
    {
        $this->provider->setBadReturnValue('invalidBadReturnValue');
        $this->provider->areDataInvalid('testData');
    }

    /**
     * @return array
     */
    public function areDataInvalidValidateResultDataProvider()
    {
        return array(
            array(ClassMetadata\Model\Provider::BAD_RETURN_VALUE_NULL, null, true),
            array(ClassMetadata\Model\Provider::BAD_RETURN_VALUE_NULL, array(), false),
            array(ClassMetadata\Model\Provider::BAD_RETURN_VALUE_FALSE, false, true),
            array(ClassMetadata\Model\Provider::BAD_RETURN_VALUE_FALSE, true, false),
            array(ClassMetadata\Model\Provider::BAD_RETURN_VALUE_EMPTY_STRING, 'test', false),
            array(ClassMetadata\Model\Provider::BAD_RETURN_VALUE_EMPTY_STRING, '', true),
            array(ClassMetadata\Model\Provider::BAD_RETURN_VALUE_EMPTY_ARRAY, array(), true),
            array(ClassMetadata\Model\Provider::BAD_RETURN_VALUE_EMPTY_ARRAY, array('test'), false)
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
