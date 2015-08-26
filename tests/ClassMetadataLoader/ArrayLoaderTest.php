<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader;

use Kassko\DataMapper\ClassMetadataLoader;

/**
 * Class ArrayLoaderTest
 * 
 * @author kko
 */
class ArrayLoaderTest extends AnnotationLoaderTest
{
    /**
     * @test
     */
    public function doLoadClassMetadataValidateCalls()
    {
        $classMetadataMock = $this->getMockBuilder(
            'Kassko\DataMapper\ClassMetadata\ClassMetadata'
        )->disableOriginalConstructor()->getMock();

        $loaderMock = $this->getMockBuilder(
            'Kassko\DataMapper\ClassMetadataLoader\ArrayLoader'
        )->setMethods(array('normalize', 'loadData'))->getMockForAbstractClass();

        $data = array('testData' => time());
        $expectedResult = 'testResult' . time();

        $loaderMock->expects($this->once())
            ->method('normalize')
            ->with($data);
        $loaderMock->expects($this->once())
            ->method('loadData')
            ->with($data);

        $result = $this->callMethod(
            $loaderMock, 
            'doLoadClassMetadata',
            [$classMetadataMock, $data]
        );

        $this->assertInstanceOf('Kassko\DataMapper\ClassMetadata\ClassMetadata', $result);
    }

    private function callMethod($object, $method, array $args)
    {
        $func = function () use ($method, $args) {
            return call_user_func_array([$this, $method], $args);
        };
        $func = $func->bindTo($object, $object);

        return $func();
    }

    /**
     * @test
     * @TODO: Verify setup 'RefDefaultSource' in InnerPhpLoader. There is no 'setRefDefaultSource' method call.
     */
    public function refDefaultSourceValidateResult()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @test
     * @TODO: Verify setup 'fieldsWithSourcesForbidden' in InnerPhpLoader. There is no 'setFieldsWithSourcesForbidden' method call.
     */
    public function excludeDefaultSourceValidateResult()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @test
     * @TODO: Verify setup 'getters' in InnerPhpLoader. There is no 'setGetters' method call.
     */
    public function getterValidateResult()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @test
     * @TODO: Verify setup 'setters' in InnerPhpLoader. There is no 'setSetters' method call.
     */
    public function setterValidateResult()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @test
     * @TODO: This test should be placed in the parent class when it fixed.
     */
    public function preExtractValidateResult()
    {
        $metadata = $this->loadMetadata('PreExtract');

        $this->assertInstanceOf('\Kassko\DataMapper\ClassMetadata\ClassMetadata', $metadata);
        $this->assertEquals(array('CustomHydratorClassName', 'preExtractMethodName'), $metadata->getOnBeforeExtract());
    }

    /**
     * @test
     * @TODO: This test should be placed in the parent class when it fixed.
     */
    public function postExtractValidateResult()
    {
        $metadata = $this->loadMetadata('PostExtract');

        $this->assertInstanceOf('\Kassko\DataMapper\ClassMetadata\ClassMetadata', $metadata);
        $this->assertEquals(array('CustomHydratorClassName', 'postExtractMethodName'), $metadata->getOnAfterExtract());
    }

    /**
     * @test
     * @TODO: This test should be placed in the parent class when it fixed.
     */
    public function preHydrateValidateResult()
    {
        $metadata = $this->loadMetadata('PreHydrate');

        $this->assertInstanceOf('\Kassko\DataMapper\ClassMetadata\ClassMetadata', $metadata);
        $this->assertEquals(array('CustomHydratorClassName', 'preHydrateMethodName'), $metadata->getOnBeforeHydrate());
    }

    /**
     * @test
     * @TODO: This test should be placed in the parent class when it fixed.
     */
    public function postHydrateValidateResult()
    {
        $metadata = $this->loadMetadata('PostHydrate');

        $this->assertInstanceOf('\Kassko\DataMapper\ClassMetadata\ClassMetadata', $metadata);
        $this->assertEquals(array('CustomHydratorClassName', 'postHydrateMethodName'), $metadata->getOnAfterHydrate());
    }
}
