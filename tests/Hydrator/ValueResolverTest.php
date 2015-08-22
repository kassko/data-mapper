<?php
namespace Kassko\DataMapperTest\Hydrator;

use Kassko\DataMapper\Hydrator\ValueResolver;
use Kassko\DataMapperTest\Hydrator\Fixture\SampleClass;

/**
 * Class ValueResolverTest
 * 
 * @author kko
 */
class ValueResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ValueResolver
     */
    private $valueResolver;

    /**
     * @return void
     */
    public function setUp()
    {
        $hydratorMock = $this->getMockBuilder(
            'Kassko\DataMapper\Hydrator\Hydrator'
        )->disableOriginalConstructor()->getMock();

        $classMetadataMock = $this->getMockBuilder(
            'Kassko\DataMapper\ClassMetadata\ClassMetadata'
        )->disableOriginalConstructor()->getMock();

        $classResolverMock = $this->getMockBuilder(
            'Kassko\ClassResolver\ClassResolverInterface'
        )->disableOriginalConstructor()->getMock();

        $this->valueResolver = $this->getMockBuilder(
            ValueResolver::class
        )->setConstructorArgs([$hydratorMock, $classMetadataMock, $classResolverMock])
         ->setMethods(['resolveRawData', 'resolveFieldValue', 'resolveService'])
         ->getMock();
    }

    /**
     * @test 
     */
    public function handleValidateBehaviourWhenObject()
    {
        $object = new SampleClass;
        $result = $this->valueResolver->handle('##this', $object);  
        $this->assertSame($object, $result);
    }

    /**
     * @test
     */
    public function handleValidateBehaviourWhenRawData()
    {
        $this->valueResolver->expects($this->once())->method('resolveRawData');
        $this->valueResolver->handle('##data', new SampleClass);  
    }

    /**
     * @test
     */
    public function handleValidateBehaviourWhenFieldValue()
    {  
        $this->valueResolver->expects($this->once())->method('resolveFieldValue')->with('property');
        $this->valueResolver->handle('#property', new SampleClass);
    }

    /**
     * @test
     */
    public function handleValidateBehaviourWhenService()
    {  
        $this->valueResolver->expects($this->once())->method('resolveService')->with('@service');
        $this->valueResolver->handle('@service', new SampleClass);
    }

    /**
     * @test
     * @expectedException \Kassko\DataMapper\Hydrator\Exception\NotResolvableValueException
     */
    public function handleValidateBehaviourWhenNotResolvableValue()
    {  
        $this->valueResolver->handle('123', new SampleClass);
    }
}
