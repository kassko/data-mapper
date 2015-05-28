<?php
namespace Kassko\DataMapperTest\ClassMetadata;

use Kassko\DataMapper\ClassMetadata;

/**
 * Class ClassMetadataEventTest
 * 
 * @author Alexey Rusnak
 */
class ClassMetadataEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ClassMetadata\ClassMetadataBuilder
     */
    protected $classMetadataBuilder;

    /**
     * @var ClassMetadata\ClassMetadata
     */
    protected $classMetadata;

    /**
     * @var ClassMetadata\ClassMetadataEvent
     */
    protected $classMetadataEvent;

    /**
     * @var array
     */
    protected $arguments = array('arg1' => 'arg1Value');

    /**
     * @return void
     */
    public function setUp()
    {
        $this->classMetadata = new ClassMetadata\ClassMetadata(
            'Kassko\DataMapperTest\ClassMetadata\Fixture\SampleClass'
        );

        $this->classMetadataBuilder = new ClassMetadata\ClassMetadataBuilder($this->classMetadata);

        $this->classMetadataEvent = new ClassMetadata\ClassMetadataEvent(
            $this->classMetadataBuilder,
            $this->arguments
        );
    }

    /**
     * @test
     */
    public function instanceOfGenericEvent()
    {
        $this->assertInstanceOf('Symfony\Component\EventDispatcher\GenericEvent', $this->classMetadataEvent);
    }

    /**
     * @test
     */
    public function getClassMetadataBuilderValidateResult()
    {
        $this->assertSame($this->classMetadataBuilder, $this->classMetadataEvent->getClassMetadataBuilder());
    }

    /**
     * @test
     */
    public function validateCallParentConstructor()
    {
        $this->assertSame($this->classMetadataBuilder, $this->classMetadataEvent->getSubject());
        $this->assertSame($this->arguments, $this->classMetadataEvent->getArguments());
    }
}
