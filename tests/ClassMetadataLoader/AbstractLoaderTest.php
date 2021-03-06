<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader;

use Kassko\DataMapper\ClassMetadataLoader;
use Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Loader;

/**
 * Class AbstractLoaderTest
 * 
 * @author Alexey Rusnak
 */
class AbstractLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Loader
     */
    protected $loader;

    /**
     * @var \Kassko\DataMapper\ClassMetadata\ClassMetadata|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $classMetadataMock;

    /**
     * @var \Kassko\DataMapperTest\ClassMetadataLoader\Fixture\LoadingCriteria|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loadingCriteriaMock;

    /**
     * @var \Kassko\DataMapper\Configuration\Configuration|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configurationMock;

    /**
     * @var \Kassko\DataMapper\ClassMetadataLoader\LoaderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loaderMock;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->loader = new Loader();

        $this->classMetadataMock = $this->getMockBuilder(
            'Kassko\DataMapper\ClassMetadata\ClassMetadata'
        )->disableOriginalConstructor()->getMock();

        $this->loadingCriteriaMock = $this->getMockBuilder(
            'Kassko\DataMapper\ClassMetadataLoader\LoadingCriteriaInterface'
        )->getMock();

        $this->configurationMock = $this->getMockBuilder(
            'Kassko\DataMapper\Configuration\Configuration'
        )->getMock();

        $this->loaderMock = $this->getMockBuilder(
            'Kassko\DataMapper\ClassMetadataLoader\AbstractLoader'
        )->getMockForAbstractClass();
    }

    /**
     * @test
     */
    public function loadClassMetadataValidateCalls()
    {
        $data = array('testData' => time());
        $expectedResult = 'testResult' . time();
        $loader = $this->getMockBuilder(
            'Kassko\DataMapper\ClassMetadataLoader\AbstractLoader'
        )->setMethods(array('doGetData', 'doLoadClassMetadata'))->getMockForAbstractClass();
        $loader->expects($this->once())
            ->method('doGetData')
            ->with($this->loadingCriteriaMock)
            ->willReturn($data);
        $loader->expects($this->once())
            ->method('doLoadClassMetadata')
            ->with($this->classMetadataMock, $data)
            ->willReturn($expectedResult);

        $this->configurationMock->expects($this->once())
            ->method('getDefaultClassMetadataResourceDir')
            ->willReturn('/tmp');
        $delegatingLoaderMock = $this->getMockBuilder(
            '\Kassko\DataMapper\ClassMetadataLoader\DelegatingLoader'
        )->disableOriginalConstructor()->getMock();

        $result = $loader->loadClassMetadata(
            $this->classMetadataMock,
            $this->loadingCriteriaMock,
            $this->configurationMock,
            $delegatingLoaderMock
        );

        $this->assertEquals($expectedResult, $result);
    }
}
