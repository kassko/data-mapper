<?php
namespace Kassko\DataMapperTest\ClassMetadata;

use Kassko\DataMapper\ClassMetadata;

/**
 * Class MethodMetadataTest
 * 
 * @author Alexey Rusnak
 */
class MethodTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ClassMetadata\Method
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
        $this->methodMetadata = new ClassMetadata\Model\Method($this->className, $this->method, $this->arguments);
    }

    /**
     * @test
     */
    public function validateConstructor()
    {
        $this->assertSame($this->className, $this->methodMetadata->getClass());
        $this->assertSame($this->method, $this->methodMetadata->getFunction());
        $this->assertSame($this->arguments, $this->methodMetadata->getArgs());
    }
}
