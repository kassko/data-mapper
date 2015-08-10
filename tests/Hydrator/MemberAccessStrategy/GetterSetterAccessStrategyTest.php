<?php
namespace Kassko\DataMapperTest\Hydrator\MemberAccessStrategy;

use Kassko\DataMapper\Hydrator\MemberAccessStrategy\GetterSetterAccessStrategy;
use Kassko\DataMapper\Hydrator\MemberAccessStrategy\PropertyAccessStrategy;
use Kassko\DataMapperTest\Hydrator\MemberAccessStrategy\Fixture\Model\ClassWithGettersSetters;

/**
 * Class PropertyAccessStrategyTest
 * 
 * @author kko
 */
class GetterSetterAccessStrategyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var GetterSetterAccessStrategy
     */
    protected $getterSetterAccessStrategy;

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
        )->setConstructorArgs([ClassWithGettersSetters::class])->setMethods(null)->getMock();

        $this->propertyAccessStrategy = new PropertyAccessStrategy;
        $this->getterSetterAccessStrategy = new GetterSetterAccessStrategy($this->propertyAccessStrategy);
    }

    /**
     * @test
     */
    public function getValueValidateResult()
    {
        $object = new ClassWithGettersSetters;

        $this->propertyAccessStrategy->prepare($object, $this->classMetadataMock);
        $this->getterSetterAccessStrategy->prepare($object, $this->classMetadataMock);

        $this->getterSetterAccessStrategy->setValue('foo', $object, 'propertyA');
        $this->getterSetterAccessStrategy->setValue(12, $object, 'propertyB');
        $nestedObject = new \Kassko\DataMapperTest\Hydrator\MemberAccessStrategy\Fixture\Model\SampleClass;
        $this->getterSetterAccessStrategy->setValue($nestedObject, $object, 'propertyC');
        $this->getterSetterAccessStrategy->setValue(123, $object, 'propertyWithNoGetterSetter');

        $this->assertEquals('foo', $this->getterSetterAccessStrategy->getValue($object, 'propertyA'));
        $this->assertEquals(12, $this->getterSetterAccessStrategy->getValue($object, 'propertyB'));
        $this->assertSame($nestedObject, $this->getterSetterAccessStrategy->getValue($object, 'propertyC'));
        $this->assertEquals(123, $this->getterSetterAccessStrategy->getValue($object, 'propertyWithNoGetterSetter'));
    }
}
