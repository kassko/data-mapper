<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader;

use Kassko\DataMapper\ClassMetadataLoader;
use Kassko\DataMapper\ClassMetadata;
use Kassko\DataMapper\Configuration\Configuration;

/**
 * Class InnerPhpLoaderTest
 *
 * @author Alexey Rusnak
 */
class InnerPhpLoaderTest extends AnnotationLoaderTest
{
    /**
     * @param string $className
     * @return ClassMetadata\ClassMetadata
     */
    public function loadMetadata($className)
    {
        $fullClassName = $this->getMetadataClassName($className);
        $loadingCriteria = ClassMetadataLoader\LoadingCriteria::create(
            '',
            '',
            $fullClassName,
            'loadInnerPhpMetadata'
        );
        $loader = new ClassMetadataLoader\InnerPhpLoader();
        return $loader->loadClassMetadata(
            new ClassMetadata\ClassMetadata($fullClassName),
            $loadingCriteria,
            new Configuration(),
            $loader
        );
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
