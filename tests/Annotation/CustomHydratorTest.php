<?php
namespace Kassko\DataMapperTest\Annotation;

use Kassko\DataMapper\Annotation;

/**
 * Class CustomHydratorTest
 * @package Kassko\DataMapperTest\Annotation
 * @author Alexey Rusnak
 */
class CustomHydratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Annotation\CustomHydrator
     */
    protected $annotation;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->annotation = new Annotation\CustomHydrator();
    }

    /**
     * @test
     */
    public function validateClassDefaultValue()
    {
        $this->assertNull($this->annotation->class);
    }

    /**
     * @test
     */
    public function validateHydrateMethodDefaultValue()
    {
        $this->assertEquals('hydrate', $this->annotation->hydrateMethod);
    }

    /**
     * @test
     */
    public function validateExtractMethodDefaultValue()
    {
        $this->assertEquals('extract', $this->annotation->extractMethod);
    }
}
