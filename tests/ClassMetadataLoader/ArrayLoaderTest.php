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

        $result = $this->callInaccessibleMethod(
            $loaderMock, 
            'doLoadClassMetadata',
            array($classMetadataMock, $data)
        );

        $this->assertInstanceOf('Kassko\DataMapper\ClassMetadata\ClassMetadata', $result);
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

    private function callInaccessibleMethod($object, $method, array $args)
    {
        $func = function () use ($method, $args) {
            return call_user_func_array(array($this, $method), $args);
        };
        $func = $func->bindTo($object, $object);

        return $func();
    }
}
