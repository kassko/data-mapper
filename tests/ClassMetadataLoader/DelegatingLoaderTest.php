<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader;

use Kassko\DataMapper\ClassMetadataLoader;

/**
 * Class DelegatingLoaderTest
 *
 * @author Alexey Rusnak
 */
class DelegatingLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ClassMetadataLoader\DelegatingLoader
     */
    protected $loader;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $loaderResolverMock;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->loaderResolverMock = $this->getMockBuilder(
            '\Kassko\DataMapper\ClassMetadataLoader\LoaderResolver'
        )->getMock();


        $this->loader = new ClassMetadataLoader\DelegatingLoader($this->loaderResolverMock);
    }

    /**
     * @test
     */
    public function instanceOfAbstractLoader()
    {
        $this->assertInstanceOf('\Kassko\DataMapper\ClassMetadataLoader\AbstractLoader', $this->loader);
    }

    /**
     * @test
     */
    public function loadClassMetadataValidateCalls()
    {
        $classMetadataMock = $this->getMockBuilder(
            '\Kassko\DataMapper\ClassMetadata\ClassMetadata'
        )->disableOriginalConstructor()->getMock();
        $loadingCriteriaMock = $this->getMockBuilder(
            '\Kassko\DataMapperTest\ClassMetadataLoader\Fixture\LoadingCriteria'
        )->getMock();
        $configurationMock = $this->getMockBuilder(
            '\Kassko\DataMapper\Configuration\Configuration'
        )->getMock();
        $loaderMock = $this->getMockBuilder(
            '\Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Loader'
        )->getMock();
        $this->loaderResolverMock->expects($this->once())
            ->method('resolveLoader')
            ->with($loadingCriteriaMock)
            ->willReturn($loaderMock);
        $loaderMock->expects($this->once())
            ->method('loadClassMetadata')
            ->with($classMetadataMock, $loadingCriteriaMock, $configurationMock, $this->loader)
            ->willReturn($classMetadataMock);
        $result = $this->loader->loadClassMetadata(
            $classMetadataMock,
            $loadingCriteriaMock,
            $configurationMock,
            $loaderMock
        );

        $this->assertSame($classMetadataMock, $result);
    }

    /**
     * @test
     * @expectedException \Kassko\DataMapper\ClassMetadataLoader\Exception\NotFoundLoaderException
     */
    public function loadClassMetadataValidateException()
    {
        $classMetadataMock = $this->getMockBuilder(
            '\Kassko\DataMapper\ClassMetadata\ClassMetadata'
        )->disableOriginalConstructor()->getMock();
        $loadingCriteriaMock = $this->getMockBuilder(
            '\Kassko\DataMapperTest\ClassMetadataLoader\Fixture\LoadingCriteria'
        )->getMock();
        $configurationMock = $this->getMockBuilder(
            '\Kassko\DataMapper\Configuration\Configuration'
        )->getMock();
        $loaderMock = $this->getMockBuilder(
            '\Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Loader'
        )->getMock();
        $this->loaderResolverMock->expects($this->once())
                                 ->method('resolveLoader')
                                 ->with($loadingCriteriaMock)
                                 ->willReturn(false);
        $this->loader->loadClassMetadata(
            $classMetadataMock,
            $loadingCriteriaMock,
            $configurationMock,
            $loaderMock
        );
    }

    /**
     * @test
     * @dataProvider supportsValidateCallsDataProvider
     * @param mixed $returnValue
     * @param bool $expectedResult
     */
    public function supportsValidateCalls($returnValue, $expectedResult)
    {
        $loadingCriteriaMock = $this->getMockBuilder(
            '\Kassko\DataMapperTest\ClassMetadataLoader\Fixture\LoadingCriteria'
        )->getMock();
        $this->loaderResolverMock->expects($this->once())
            ->method('resolveLoader')
            ->with($loadingCriteriaMock)
            ->willReturn($returnValue);
        $result = $this->loader->supports($loadingCriteriaMock);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function supportsValidateCallsDataProvider()
    {
        return array(
            array(true, true),
            array(false, false),
            array(1, true),
            array(0, true),
            array('', true),
            array(null, true)
        );
    }

    /**
     * @test
     */
    public function getDataValidateCalls()
    {
        $loadingCriteriaMock = $this->getMockBuilder(
            '\Kassko\DataMapperTest\ClassMetadataLoader\Fixture\LoadingCriteria'
        )->getMock();
        $configurationMock = $this->getMockBuilder(
            '\Kassko\DataMapper\Configuration\Configuration'
        )->getMock();
        $loaderMock = $this->getMockBuilder(
            '\Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Loader'
        )->getMock();
        $this->loaderResolverMock->expects($this->once())
                                 ->method('resolveLoader')
                                 ->with($loadingCriteriaMock)
                                 ->willReturn($loaderMock);
        $loaderMock->expects($this->once())
                   ->method('getData')
                   ->with($loadingCriteriaMock, $configurationMock, $loaderMock)
                   ->willReturn($loaderMock);
        $result = $this->loader->getData(
            $loadingCriteriaMock,
            $configurationMock,
            $loaderMock
        );

        $this->assertEquals($loaderMock, $result);
    }

    /**
     * @test
     * @expectedException \Kassko\DataMapper\ClassMetadataLoader\Exception\NotFoundLoaderException
     */
    public function getDataValidateException()
    {
        $loadingCriteriaMock = $this->getMockBuilder(
            '\Kassko\DataMapperTest\ClassMetadataLoader\Fixture\LoadingCriteria'
        )->getMock();
        $configurationMock = $this->getMockBuilder(
            '\Kassko\DataMapper\Configuration\Configuration'
        )->getMock();
        $loaderMock = $this->getMockBuilder(
            '\Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Loader'
        )->getMock();
        $this->loaderResolverMock->expects($this->once())
                                 ->method('resolveLoader')
                                 ->with($loadingCriteriaMock)
                                 ->willReturn(false);
        $this->loader->getData(
            $loadingCriteriaMock,
            $configurationMock,
            $loaderMock
        );
    }
}
