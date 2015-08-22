<?php
namespace Kassko\DataMapperTest\Hydrator\MemberAccessStrategy;

use Kassko\DataMapper\Hydrator\MemberAccessStrategy\PropertyAccessStrategy;
use Kassko\DataMapperTest\Hydrator\MemberAccessStrategy\Fixture\Model\ClassWithOnlyProperties;

/**
 * Class PropertyAccessStrategyTest
 * 
 * @author kko
 */
class PropertyAccessStrategyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PropertyAccessStrategy
     */
    protected $propertyAccessStrategy;

    /**
     * @var \Kassko\DataMapper\ClassMetadata\ClassMetadata|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $classMetadataMock;

    public function setUp()
    {
        $this->classMetadataMock = $this->getMockBuilder(
            'Kassko\DataMapper\ClassMetadata\ClassMetadata'
        )->setConstructorArgs([ClassWithOnlyProperties::class])->setMethods(null)->getMock();

        $this->propertyAccessStrategy = new PropertyAccessStrategy;
    }

    /**
     * @test
     */
    public function getValueValidateResult()
    {
        $object = new ClassWithOnlyProperties;
        
        $this->propertyAccessStrategy->prepare($object, $this->classMetadataMock);

        $this->propertyAccessStrategy->setValue('foo', $object, 'propertyA');
        $this->propertyAccessStrategy->setValue(12, $object, 'propertyB');
        $nestedObject = new \Kassko\DataMapperTest\Hydrator\MemberAccessStrategy\Fixture\Model\SampleClass;
        $this->propertyAccessStrategy->setValue($nestedObject, $object, 'propertyC');

        $this->assertEquals('foo', $this->propertyAccessStrategy->getValue($object, 'propertyA'));
        $this->assertEquals(12, $this->propertyAccessStrategy->getValue($object, 'propertyB'));
        $this->assertSame($nestedObject, $this->propertyAccessStrategy->getValue($object, 'propertyC'));
    }
}
