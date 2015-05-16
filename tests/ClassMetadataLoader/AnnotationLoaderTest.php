<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Kassko\DataMapper\ClassMetadataLoader;
use Kassko\DataMapper\ClassMetadata;
use Kassko\DataMapper\Configuration\Configuration;

/**
 * Class AbstractLoaderTest
 * @package Kassko\DataMapperTest\ClassMetadataLoader
 * @author Alexey Rusnak
 */
class AnnotationLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $className = '\Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Annotations';

    /**
     * @var ClassMetadataLoader\AnnotationLoader
     */
    protected $loader;

    /**
     * @var AnnotationReader
     */
    protected $reader;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->reader = new AnnotationReader();
        $this->loader = new ClassMetadataLoader\AnnotationLoader($this->reader);
    }

    /**
     * @test
     */
    public function dataSourcesStoreValidateResult()
    {
        $className = '\Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Annotation\DataSourcesStore';
        $resourcePath = '/tmp/resourcePath';
        $resourceType = '';
        $resourceClass = '';
        $resourceMethod = '';
        $classMetadata = new ClassMetadata\ClassMetadata($className);
        $loadingCriteria = ClassMetadataLoader\LoadingCriteria::create(
            $resourcePath,
            $resourceType,
            $resourceClass,
            $resourceMethod
        );

        $loader = new ClassMetadataLoader\AnnotationLoader(new AnnotationReader());
        AnnotationRegistry::registerLoader('class_exists');
        $metadata = $loader->loadClassMetadata($classMetadata, $loadingCriteria, new Configuration());

        $this->assertInstanceOf('\Kassko\DataMapper\ClassMetadata\ClassMetadata', $metadata);
        /**
         * @var ClassMetadata\SourcePropertyMetadata $dataSource
         */
        $dataSource = $metadata->findDataSourceById('personSource');
        $this->assertInstanceOf('\Kassko\DataMapper\ClassMetadata\SourcePropertyMetadata', $dataSource);

        $this->assertEquals('Kassko\Sample\PersonDataSource', $dataSource->class);
        $this->assertEquals('getData', $dataSource->method);
        $this->assertEquals('checkException', $dataSource->onFail);
        $this->assertEquals('\RuntimeException', $dataSource->exceptionClass);
        $this->assertEquals('emptyString', $dataSource->badReturnValue);
        $this->assertEquals('testFallbackSourceId', $dataSource->fallbackSourceId);
        $this->assertEquals(array('#id'), $dataSource->args);
        //TODO: Validate: multiple depends, 'preprocessor' and 'processor'.
        $this->assertEquals(array('#dependsFirst'), $dataSource->depends);
        $this->assertTrue($dataSource->supplySeveralFields);
        $this->assertTrue($dataSource->lazyLoading);
    }

    /**
     * @test
     */
    public function objectValidateResult()
    {
        $className = '\Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Annotation\Object';
        $resourcePath = '/tmp/resourcePath';
        $resourceType = '';
        $resourceClass = '';
        $resourceMethod = '';
        $classMetadata = new ClassMetadata\ClassMetadata($className);
        $loadingCriteria = ClassMetadataLoader\LoadingCriteria::create(
            $resourcePath,
            $resourceType,
            $resourceClass,
            $resourceMethod
        );

        $loader = new ClassMetadataLoader\AnnotationLoader(new AnnotationReader());
        AnnotationRegistry::registerLoader('class_exists');
        $metadata = $loader->loadClassMetadata($classMetadata, $loadingCriteria, new Configuration());

        $this->assertInstanceOf('\Kassko\DataMapper\ClassMetadata\ClassMetadata', $metadata);

        $this->assertEquals('exclude_all', $metadata->getFieldExclusionPolicy());
        $this->assertEquals('testProviderClass', $metadata->getRepositoryClass());
        $this->assertEquals('testReadDateConverter', $metadata->getObjectReadDateFormat());
        $this->assertEquals('testWriteDateConverter', $metadata->getObjectWriteDateFormat());
        $this->assertEquals('testFieldMappingExtensionClass', $metadata->getPropertyMetadataExtensionClass());
        $this->assertEquals('testClassMappingExtensionClass', $metadata->getClassMetadataExtensionClass());
        $this->assertTrue($metadata->isPropertyAccessStrategyEnabled());
    }
}