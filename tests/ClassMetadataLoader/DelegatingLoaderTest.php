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
    public function instanceOfLoaderInterface()
    {
        $this->assertInstanceOf('\Kassko\DataMapper\ClassMetadataLoader\LoaderInterface', $this->loader);
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
            'Kassko\DataMapper\ClassMetadataLoader\LoadingCriteria'
        )->disableOriginalConstructor()->getMock();
        $configurationMock = $this->getMockBuilder(
            '\Kassko\DataMapper\Configuration\Configuration'
        )->getMock();
        $delegatingLoaderMock = $this->getMockBuilder(
            '\Kassko\DataMapper\ClassMetadataLoader\DelegatingLoader'
        )->disableOriginalConstructor()->getMock();
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
            $delegatingLoaderMock
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
            'Kassko\DataMapper\ClassMetadataLoader\LoadingCriteria'
        )->disableOriginalConstructor()->getMock();
        $configurationMock = $this->getMockBuilder(
            '\Kassko\DataMapper\Configuration\Configuration'
        )->getMock();
        $delegatingLoaderMock = $this->getMockBuilder(
            '\Kassko\DataMapper\ClassMetadataLoader\DelegatingLoader'
        )->disableOriginalConstructor()->getMock();
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
            $delegatingLoaderMock
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
            'Kassko\DataMapper\ClassMetadataLoader\LoadingCriteria'
        )->disableOriginalConstructor()->getMock();
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
     * @expectedException \Kassko\DataMapper\ClassMetadataLoader\Exception\NotFoundLoaderException
     */
    public function getDelegatedLoaderValidateException()
    {
        $loadingCriteriaMock = $this->getMockBuilder(
            'Kassko\DataMapper\ClassMetadataLoader\LoadingCriteria'
        )->disableOriginalConstructor()->getMock();
        $this->loaderResolverMock->expects($this->once())
                                 ->method('resolveLoader')
                                 ->with($loadingCriteriaMock)
                                 ->willReturn(false);
        $this->loader->getDelegatedLoader(
            $loadingCriteriaMock
        );
    }
}
