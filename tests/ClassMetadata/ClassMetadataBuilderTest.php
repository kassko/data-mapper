<?php
namespace Kassko\DataMapperTest\ClassMetadata;

use Kassko\DataMapper\ClassMetadata;

/**
 * Class ClassMetadataBuilderTest
 * 
 * @author Alexey Rusnak
 */
class ClassMetadataBuilderTest extends \PHPUnit_Framework_TestCase
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
     * @return void
     */
    public function setUp()
    {
        $this->classMetadata = new ClassMetadata\ClassMetadata('Kassko\DataMapperTest\ClassMetadata\Fixture\SampleClass');

        $this->classMetadataBuilder = new ClassMetadata\ClassMetadataBuilder($this->classMetadata);
    }

    /**
     * @test
     */
    public function getClassMetadataValidateResult()
    {
        $this->assertSame($this->classMetadata, $this->classMetadataBuilder->getClassMetadata());
    }
}
