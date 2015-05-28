<?php
namespace Kassko\DataMapperTest\ClassMetadata;

use Kassko\DataMapper\ClassMetadata;
use Kassko\DataMapper\Configuration;
use Kassko\DataMapper\ClassMetadataLoader;
use Kassko\DataMapper\Cache;
use Symfony\Component\EventDispatcher;

/**
 * Class ClassMetadataFactoryTest
 * @package Kassko\DataMapperTest\ClassMetadata
 * @author Alexey Rusnak
 */
class ClassMetadataFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ClassMetadata\ClassMetadataFactory
     */
    protected $classMetadataFactory;

    /**
     * @var Configuration\ObjectKey|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectKeyMock;

    /**
     * @var ClassMetadataLoader\LoadingCriteria|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loadingCriteriaMock;

    /**
     * @var Configuration\Configuration|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configurationMock;

    /**
     * @var Cache\ArrayCache|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cacheMock;

    /**
     * @var EventDispatcher\EventDispatcherInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventDispatcherMock;

    /**
     * @var ClassMetadataLoader\AbstractLoader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loaderMock;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->objectKeyMock = $this->getMockBuilder('Kassko\DataMapper\Configuration\ObjectKey')
            ->disableOriginalConstructor()
            ->getMock();
        $this->loadingCriteriaMock = $this->getMockBuilder(
            'Kassko\DataMapperTest\ClassMetadataLoader\Fixture\LoadingCriteria')->getMock();
        $this->configurationMock = $this->getMockBuilder('Kassko\DataMapper\Configuration\Configuration')->getMock();
        $this->cacheMock = $this->getMockBuilder('Kassko\DataMapper\Cache\ArrayCache')->getMock();
        $this->eventDispatcherMock = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcher')
            ->getMock();
        $this->loaderMock = $this->getMockBuilder('Kassko\DataMapper\ClassMetadataLoader\AbstractLoader')
            ->setMethods(array('loadClassMetadata'))
            ->getMockForAbstractClass();

        $this->classMetadataFactory = new ClassMetadata\ClassMetadataFactory();
        $this->classMetadataFactory->setEventManager($this->eventDispatcherMock);
        $this->classMetadataFactory->setClassMetadataLoader($this->loaderMock);
        $this->classMetadataFactory->setCache($this->cacheMock);
    }

    /**
     * @test
     */
    public function instanceOfClassMetadataFactoryInterface()
    {
        $this->assertInstanceOf('Kassko\DataMapper\ClassMetadata\ClassMetadataFactoryInterface', $this->classMetadataFactory);
    }

    /**
     * @test
     */
    public function instanceOfClassMetadataFactoryOptionsAwareInterface()
    {
        $this->assertInstanceOf('Kassko\DataMapper\ClassMetadata\ClassMetadataFactoryOptionsAwareInterface', $this->classMetadataFactory);
    }

    /**
     * @test
     */
    public function setEventManagerValidateResult()
    {
        $result = $this->classMetadataFactory->setEventManager($this->eventDispatcherMock);

        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function setClassMetadataLoaderValidateResult()
    {
        $result = $this->classMetadataFactory->setClassMetadataLoader($this->loaderMock);

        $this->assertSame($this->classMetadataFactory, $result);
    }

    /**
     * @test
     */
    public function setCacheValidateResult()
    {
        $result = $this->classMetadataFactory->setCache($this->cacheMock);

        $this->assertSame($this->classMetadataFactory, $result);
    }

    /**
     * @test
     */
    public function loadMetadataFetchFromCache()
    {
        $expectedResult = 'testExpectedResult' . time();
        $cacheKey = 'testCacheKey';
        $this->objectKeyMock->expects($this->once())
             ->method('getKey')
             ->willReturn($cacheKey);
        $this->cacheMock->expects($this->once())
             ->method('contains')
             ->willReturn(true);
        $this->cacheMock->expects($this->once())
                        ->method('fetch')
                        ->willReturn($expectedResult);

        $result = $this->classMetadataFactory->loadMetadata(
            $this->objectKeyMock,
            $this->loadingCriteriaMock,
            $this->configurationMock
        );

        $this->assertSame($expectedResult, $result);
    }

    /**
     * @test
     */
    public function loadMetadataCreateNewClassMetadata()
    {
        $expectedResult = new ClassMetadata\ClassMetadata('Kassko\DataMapperTest\ClassMetadata\Fixture\SampleClass');
        $cacheKey = 'testCacheKey';
        $this->objectKeyMock->expects($this->once())
                            ->method('getKey')
                            ->willReturn($cacheKey);
        $this->objectKeyMock->expects($this->once())
                            ->method('getClass')
                            ->willReturn('Kassko\DataMapperTest\ClassMetadata\Fixture\SampleClass');
        $this->loaderMock->expects($this->once())
             ->method('loadClassMetadata')
             ->with(
                 $expectedResult,
                 $this->loadingCriteriaMock,
                 $this->configurationMock
             );
        $this->cacheMock->expects($this->once())
             ->method('save')
             ->with($cacheKey);
        $this->eventDispatcherMock->expects($this->once())
             ->method('dispatch')
             ->with(ClassMetadata\Events::POST_LOAD_METADATA);

        $result = $this->classMetadataFactory->loadMetadata(
            $this->objectKeyMock,
            $this->loadingCriteriaMock,
            $this->configurationMock
        );

        $this->assertEquals($expectedResult, $result);
    }
}
