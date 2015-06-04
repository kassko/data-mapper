<?php
namespace Kassko\DataMapperTest\ClassMetadata;

use Kassko\DataMapper\ClassMetadata;

/**
 * Class MethodMetadataTest
 * 
 * @author Alexey Rusnak
 */
class MethodMetadataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ClassMetadata\MethodMetadata
     */
    protected $methodMetadata;

    /**
     * @var string
     */
    protected $className = 'Kassko\DataMapperTest\ClassMetadata\Fixture\SampleClass';

    /**
     * @var string
     */
    protected $method = '';

    /**
     * @var array
     */
    protected $arguments = array();

    /**
     * @return void
     */
    public function setUp()
    {
        $this->methodMetadata = new ClassMetadata\MethodMetadata($this->className, $this->method, $this->arguments);
    }

    /**
     * @test
     */
    public function validateConstructor()
    {
        $this->assertSame($this->className, $this->methodMetadata->class);
        $this->assertSame($this->method, $this->methodMetadata->method);
        $this->assertSame($this->arguments, $this->methodMetadata->args);
    }
}
