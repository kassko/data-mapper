<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Kassko\DataMapper\ClassMetadataLoader;
use Kassko\DataMapper\ClassMetadata;
use Kassko\DataMapper\Configuration\Configuration;

/**
 * Class AnnotationLoaderTest
 * 
 * @author Alexey Rusnak
 */
class AnnotationLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $namespace = '\Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Metadata';

    /**
     * @inheritdoc
     */
    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        AnnotationRegistry::registerLoader('class_exists');
    }

    /**
     * @param string $className
     * @return string
     */
    public function getMetadataClassName($className)
    {
        return $this->namespace . '\\' . $className;
    }

    /**
     * @param string $className
     * @return ClassMetadata\ClassMetadata
     */
    public function loadMetadata($className)
    {
        $fullClassName = $this->getMetadataClassName($className);
        $classMetadata = new ClassMetadata\ClassMetadata($fullClassName);
        $loadingCriteria = ClassMetadataLoader\LoadingCriteria::create(
            sys_get_temp_dir(),
            '',
            $fullClassName,
            ''
        );

        $loader = new ClassMetadataLoader\AnnotationLoader(new AnnotationReader());
        return $loader->loadClassMetadata($classMetadata, $loadingCriteria, new Configuration());
    }

    /**
     * @test
     */
    public function dataSourcesStoreValidateResult()
    {
        $metadata = $this->loadMetadata('DataSourcesStore');

        $this->assertInstanceOf('\Kassko\DataMapper\ClassMetadata\ClassMetadata', $metadata);
        /**
         * @var ClassMetadata\Model\DataSource $dataSource
         */
        $dataSource = $metadata->findDataSourceById('personSource');
        $this->assertInstanceOf('\Kassko\DataMapper\ClassMetadata\Model\DataSource', $dataSource);

        $this->assertInstanceOf('\Kassko\DataMapper\ClassMetadata\Model\Method', $dataSource->getMethod());
        $this->assertEquals('Kassko\Sample\PersonDataSource', $dataSource->getMethod()->getClass());
        $this->assertEquals('getData', $dataSource->getMethod()->getFunction());
        $this->assertEquals(array('#id'), $dataSource->getMethod()->getArgs());
        $this->assertEquals('checkException', $dataSource->getOnFail());
        $this->assertEquals('\RuntimeException', $dataSource->getExceptionClass());
        $this->assertEquals('emptyString', $dataSource->getBadReturnValue());
        $this->assertEquals('testFallbackSourceId', $dataSource->getFallbackSourceId());

        $this->assertInstanceOf('\Kassko\DataMapper\ClassMetadata\Model\Method', $dataSource->getPreprocessors()[0]);
        $this->assertInstanceOf('\Kassko\DataMapper\ClassMetadata\Model\Method', $dataSource->getProcessors()[0]);
       
        $this->assertEquals(array('#dependsFirst'), $dataSource->getDepends());
        $this->assertTrue($dataSource->getSupplySeveralFields());
        $this->assertTrue($dataSource->getLazyLoading());
    }

    /**
     * @test
     */
    public function dataSourcesStoreMultiplesDependsValidateResult()
    {
        $metadata = $this->loadMetadata('DataSourcesStoreMultiplesDepends');

        /**
         * @var ClassMetadata\Model\DataSource $dataSource
         */
        $dataSource = $metadata->findDataSourceById('personSource');

        $this->assertEquals(array('#dependsFirst', '#dependsSecond', '#dependsThird'),  $dataSource->getDepends());
    }

    /**
     * @test
     */
    public function dataSourcesStoreMultiplesProcessorsValidateResult()
    {
        $metadata = $this->loadMetadata('DataSourcesStoreMultiplesProcessors');

        /**
         * @var ClassMetadata\Model\DataSource $dataSource
         */
        $dataSource = $metadata->findDataSourceById('personSource');

        $preprocessors = $dataSource->getPreprocessors();
        $processors = $dataSource->getPreprocessors();

        $this->assertCount(2, $preprocessors);
        $this->assertCount(2, $processors);
        $this->assertContainsOnlyInstancesOf(
            '\Kassko\DataMapper\ClassMetadata\Model\Method',
            $preprocessors
        );
        $this->assertContainsOnlyInstancesOf(
            '\Kassko\DataMapper\ClassMetadata\Model\Method',
            $processors
        );
        /**
         * @var \Kassko\DataMapper\ClassMetadata\Model\Method $methodMetadata
         */
        $methodMetadata = $preprocessors[0];
        $this->assertEquals('somePrepocessorA', $methodMetadata->getFunction());
        $this->assertEquals('##this', $methodMetadata->getClass());
        $this->assertEquals(array(), $methodMetadata->getArgs());

        $methodMetadata = $preprocessors[1];
        $this->assertEquals('somePrepocessorB', $methodMetadata->getFunction());
        $this->assertEquals('##this', $methodMetadata->getClass());
        $this->assertEquals(array(), $methodMetadata->getArgs());

        $methodMetadata = $processors[0];
        $this->assertEquals('someProcessorA', $methodMetadata->getFunction());
        $this->assertEquals('##this', $methodMetadata->getClass());
        $this->assertEquals(array(), $methodMetadata->getArgs());

        $methodMetadata = $processors[1];
        $this->assertEquals('someProcessorB', $methodMetadata->getFunction());
        $this->assertEquals('##this', $methodMetadata->getClass());
        $this->assertEquals(array(), $methodMetadata->getArgs());
    }

    /**
     * @test
     */
    public function objectValidateResult()
    {
        $metadata = $this->loadMetadata('Object');

        $this->assertInstanceOf('\Kassko\DataMapper\ClassMetadata\ClassMetadata', $metadata);

        $this->assertEquals('exclude_all', $metadata->getFieldExclusionPolicy());
        $this->assertEquals('testProviderClass', $metadata->getRepositoryClass());
        $this->assertEquals('testReadDateConverter', $metadata->getObjectReadDateFormat());
        $this->assertEquals('testWriteDateConverter', $metadata->getObjectWriteDateFormat());
        $this->assertEquals('testFieldMappingExtensionClass', $metadata->getPropertyMetadataExtensionClass());
        $this->assertEquals('testClassMappingExtensionClass', $metadata->getClassMetadataExtensionClass());
        $this->assertTrue($metadata->isPropertyAccessStrategyEnabled());
    }

    /**
     * @test
     */
    public function providersStoreValidateResult()
    {
        $metadata = $this->loadMetadata('ProvidersStore');

        $this->assertInstanceOf('\Kassko\DataMapper\ClassMetadata\ClassMetadata', $metadata);
        
        /**
         * @var $provider
         */
        $provider = $metadata->findProviderById('providers#1');
        $this->assertInstanceOf('\Kassko\DataMapper\ClassMetadata\Model\Provider', $provider);
        $this->assertEquals('providers#1', $provider->getId());
        $this->assertTrue($provider->getLazyLoading());
        $this->assertTrue($provider->getSupplySeveralFields());
        $this->assertInternalType('array', $provider->getDepends());
        $this->assertEquals(1, count($provider->getDepends()));
        foreach ($provider->getDepends() as $dependency) {
            $this->assertEquals('depend#1', $dependency);
        }
        $this->assertEquals('\RuntimeException', $provider->getExceptionClass());
        $this->assertEquals('emptyArray', $provider->getBadReturnValue());
        $this->assertEquals('fallbackSourceId#1', $provider->getFallbackSourceId());
        $this->assertInstanceOf('\Kassko\DataMapper\ClassMetadata\Model\Method', $provider->getMethod());
        $this->assertEquals('class', $provider->getMethod()->getClass());
        $this->assertEquals('method', $provider->getMethod()->getFunction());
        $this->assertEquals('arg#1', $provider->getMethod()->getArgs()[0]);
    }

    /**
     * @test
     */
    public function providersStoreMultiplesDependsValidateResult()
    {
        $metadata = $this->loadMetadata('ProvidersStoreMultiplesDepends');

        /**
         * @var ClassMetadata\Model\Provider $dataSource
         */
        $provider = $metadata->findProviderById('personSource');

        $this->assertEquals(array('#dependsFirst', '#dependsSecond', '#dependsThird'),  $provider->getDepends());
    }

    /**
     * @test
     */
    public function providersStoreMultiplesProcessorsValidateResult()
    {
        $metadata = $this->loadMetadata('ProvidersStoreMultiplesProcessors');

        /**
         * @var ClassMetadata\Model\Provider $provider
         */
        $provider = $metadata->findProviderById('personSource');

        $this->assertContainsOnlyInstancesOf('\Kassko\DataMapper\ClassMetadata\Model\Method', $provider->getPreprocessors());
        $this->assertContainsOnlyInstancesOf('\Kassko\DataMapper\ClassMetadata\Model\Method', $provider->getProcessors());
    }

    /**
     * @test
     */
    public function refDefaultSourceValidateResult()
    {
        $metadata = $this->loadMetadata('RefDefaultSource');

        $this->assertInstanceOf('\Kassko\DataMapper\ClassMetadata\ClassMetadata', $metadata);
        $this->assertEquals('refDefaultSourceId', $metadata->getRefDefaultSource());
    }

    /**
     * @test
     */
    public function customHydratorValidateResult()
    {
        $metadata = $this->loadMetadata('CustomHydrator');

        $this->assertInstanceOf('\Kassko\DataMapper\ClassMetadata\ClassMetadata', $metadata);

        $customHydrationInfo = $metadata->getCustomHydratorInfo();
        $this->assertInternalType('array', $customHydrationInfo);
        $this->assertEquals('CustomHydratorClassName', $customHydrationInfo[0]);
        $this->assertEquals('hydrateMethod', $customHydrationInfo[1]);
        $this->assertEquals('extractMethod', $customHydrationInfo[2]);
    }

    /**
     * @test
     * @TODO: Check 'onBeforeExtract' setting up. AnnotationLoader use 'method' attribute only.
     */
    public function preExtractValidateResult()
    {
        $metadata = $this->loadMetadata('PreExtract');

        $this->assertInstanceOf('\Kassko\DataMapper\ClassMetadata\ClassMetadata', $metadata);
        $this->assertEquals('preExtractMethodName', $metadata->getOnBeforeExtract());
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @test
     * @TODO: Check 'onAfterExtract' setting up. AnnotationLoader use 'method' attribute only.
     */
    public function postExtractValidateResult()
    {
        $metadata = $this->loadMetadata('PostExtract');

        $this->assertInstanceOf('\Kassko\DataMapper\ClassMetadata\ClassMetadata', $metadata);
        $this->assertEquals('postExtractMethodName', $metadata->getOnAfterExtract());
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @test
     * @TODO: Check 'onBeforeHydrate' setting up. AnnotationLoader use 'method' attribute only.
     */
    public function preHydrateValidateResult()
    {
        $metadata = $this->loadMetadata('PreHydrate');

        $this->assertInstanceOf('\Kassko\DataMapper\ClassMetadata\ClassMetadata', $metadata);
        $this->assertEquals('preHydrateMethodName', $metadata->getOnBeforeHydrate());
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @test
     * @TODO: Check 'onAfterHydrate' setting up. AnnotationLoader use 'method' attribute only.
     */
    public function postHydrateValidateResult()
    {
        $metadata = $this->loadMetadata('PostHydrate');

        $this->assertInstanceOf('\Kassko\DataMapper\ClassMetadata\ClassMetadata', $metadata);
        $this->assertEquals('postHydrateMethodName', $metadata->getOnAfterHydrate());
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @test
     */
    public function objectListenersValidateResult()
    {
        $metadata = $this->loadMetadata('ObjectListeners');

        $this->assertInstanceOf('\Kassko\DataMapper\ClassMetadata\ClassMetadata', $metadata);
        $this->assertEquals(array('classList#1'), $metadata->getObjectListenerClasses());
    }

    /**
     * @test
     */
    public function fieldValidateResult()
    {
        $metadata = $this->loadMetadata('Field');

        $this->assertInstanceOf('\Kassko\DataMapper\ClassMetadata\ClassMetadata', $metadata);
        $this->assertEquals(
            array(
                'fieldOne'  => array(
                    'field' => array(
                        'name'                       => 'FirstField',
                        'type'                       => 'string',
                        'class'                      => 'stdClass',
                        'readConverter'              => 'readConvertFirstField',
                        'writeConverter'             => 'writeConvertFirstField',
                        'readDateConverter'          => '',
                        'writeDateConverter'         => '',
                        'fieldMappingExtensionClass' => 'ExtensionClass'
                    )
                ),
                'fieldTwo'  => array(
                    'field' => array(
                        'name'                       => 'SecondField',
                        'type'                       => 'integer',
                        'class'                      => '\DateTime',
                        'readConverter'              => '',
                        'writeConverter'             => '',
                        'readDateConverter'          => 'readDateConvertSecondField',
                        'writeDateConverter'         => 'writeDateConvertSecondField',
                        'fieldMappingExtensionClass' => 'ExtensionClass'
                    )
                ),
                'dateField'  => array(
                    'field' => array(
                        'name'                       => 'DateField',
                        'type'                       => 'date',
                        'class'                      => '',
                        'readConverter'              => '',
                        'writeConverter'             => '',
                        'readDateConverter'          => '',
                        'writeDateConverter'         => '',
                        'fieldMappingExtensionClass' => ''
                    )
                )
            ),
            $metadata->getFieldsDataByKey()
        );
        $this->assertEquals(array('dateField'), $metadata->getMappedDateFieldNames());
        // @TODO: Need to verify INDEX_EXTENSION_CLASS. Possibly error, unknown attribute 'mappingExtensionClass' used.
        $this->assertEquals(
            array(
                'fieldOne'  => array(
                    ClassMetadata\ClassMetadata::INDEX_EXTRACTION_STRATEGY  => 'writeConvertFirstField',
                    ClassMetadata\ClassMetadata::INDEX_HYDRATION_STRATEGY   => 'readConvertFirstField',
                    ClassMetadata\ClassMetadata::INDEX_EXTENSION_CLASS      => ''
                )
            ),
            $metadata->getFieldsWithHydrationStrategy()
        );
    }

    /**
     * @test
     */
    public function excludeValidateResult()
    {
        $metadata = $this->loadMetadata('Exclude');

        $this->assertInstanceOf('\Kassko\DataMapper\ClassMetadata\ClassMetadata', $metadata);
        $this->assertEquals('include_all', $metadata->getFieldExclusionPolicy());
        $this->assertTrue($metadata->isNotManaged('excludedField'));
        $this->assertFalse($metadata->isNotManaged('field'));
    }

    /**
     * @test
     */
    public function dataSourceValidateResult()
    {
        $metadata = $this->loadMetadata('DataSource');

        $this->assertInstanceOf('\Kassko\DataMapper\ClassMetadata\ClassMetadata', $metadata);

        $this->assertEquals(
            array(
                'firstField' => array(
                    'id'                  => 'firstFieldId',
                    'lazyLoading'         => 1,
                    'supplySeveralFields' => 1,
                    'depends'             => array('depend#1', 'depend#2'),
                    'onFail'              => 'checkException',
                    'exceptionClass'      => '\RuntimeException',
                    'badReturnValue'      => 'emptyString',
                    'fallbackSourceId'    => 'firstFieldFallbackSourceId',
                    'preprocessor'        => array(
                        'class'  => '##this',
                        'method' => 'fooPreprocessor',
                        'args'   => array()
                    ),
                    'processor'           => array(
                        'class'  => '##this',
                        'method' => 'barProcessor',
                        'args'   => array()
                    ),
                    'preprocessors'       => array(),
                    'processors'          => array(),
                    'class'               => '\stdClass',
                    'method'              => 'someMethod',
                    'args'                => array('argument#1', 'argument#2')
                )
            ),
            $metadata->getDataSources()
        );
        $this->assertEquals(array(), $metadata->getProviders());
    }

    /**
     * @test
     */
    public function providerValidateResult()
    {
        $metadata = $this->loadMetadata('Provider');

        $this->assertInstanceOf('\Kassko\DataMapper\ClassMetadata\ClassMetadata', $metadata);

        $this->assertEquals(
            array(
                'providerField' => array(
                    'id'                  => 'providerFieldId',
                    'lazyLoading'         => 1,
                    'supplySeveralFields' => 1,
                    'depends'             => array('depend#1', 'depend#2'),
                    'onFail'              => 'checkException',
                    'exceptionClass'      => '\RuntimeException',
                    'badReturnValue'      => 'emptyString',
                    'fallbackSourceId'    => 'firstFieldFallbackSourceId',
                    'preprocessor'        => array(
                        'class'  => '##this',
                        'method' => 'fooPreprocessor',
                        'args'   => array()
                    ),
                    'processor'           => array(
                        'class'  => '##this',
                        'method' => 'barProcessor',
                        'args'   => array()
                    ),
                    'preprocessors'       => array(),
                    'processors'          => array(),
                    'class'               => '\stdClass',
                    'method'              => 'someMethod',
                    'args'                => array('argument#1', 'argument#2')
                )
            ),
            $metadata->getProviders()
        );
        $this->assertEquals(array(), $metadata->getDataSources());
    }

    /**
     * @test
     */
    public function excludeDefaultSourceValidateResult()
    {
        $metadata = $this->loadMetadata('ExcludeDefaultSource');

        $this->assertInstanceOf('\Kassko\DataMapper\ClassMetadata\ClassMetadata', $metadata);
        $this->assertEquals(array('excludeDefaultSourceField' => true), $metadata->getFieldsWithSourcesForbidden());
    }

    /**
     * @test
     */
    public function valueObjectValidateResult()
    {
        $metadata = $this->loadMetadata('ValueObject');

        $this->assertInstanceOf('\Kassko\DataMapper\ClassMetadata\ClassMetadata', $metadata);
        $this->assertEquals(
            array(
                '\ValueObjectClass', 'valueObjectResourcePath/valueObjectResourceName', 'valueObjectResourceType'
            ),
            $metadata->getValueObjectInfo('firstField')
        );
    }

    /**
     * @test
     */
    public function idValidateResult()
    {
        $metadata = $this->loadMetadata('Id');

        $this->assertInstanceOf('\Kassko\DataMapper\ClassMetadata\ClassMetadata', $metadata);
        $this->assertEquals('firstField', $metadata->getMappedIdFieldName());
    }

    /**
     * @test
     */
    public function idCompositePartValidateResult()
    {
        $metadata = $this->loadMetadata('IdCompositePart');

        $this->assertInstanceOf('\Kassko\DataMapper\ClassMetadata\ClassMetadata', $metadata);
        $this->assertEquals(array('firstField', 'secondField'), $metadata->getMappedIdCompositePartFieldName());
    }

    /**
     * @test
     */
    public function versionValidateResult()
    {
        $metadata = $this->loadMetadata('Version');

        $this->assertInstanceOf('\Kassko\DataMapper\ClassMetadata\ClassMetadata', $metadata);
        $this->assertEquals('firstField', $metadata->getMappedVersionFieldName());
    }

    /**
     * @test
     */
    public function transientValidateResult()
    {
        $metadata = $this->loadMetadata('Transient');

        $this->assertInstanceOf('\Kassko\DataMapper\ClassMetadata\ClassMetadata', $metadata);
        $this->assertTrue($metadata->isTransient('firstField'));
        $this->assertFalse($metadata->isTransient('secondField'));
    }

    /**
     * @test
     */
    public function refSourceValidateResult()
    {
        $metadata = $this->loadMetadata('RefSource');

        $metadata->compile();
        $this->assertInstanceOf('\Kassko\DataMapper\ClassMetadata\ClassMetadata', $metadata);
        $this->assertTrue($metadata->hasProvider('firstField'));

        $this->assertEquals(
            array(
                'secondField' => array(
                    'id'                  => 'firstFieldId',
                    'lazyLoading'         => 1,
                    'supplySeveralFields' => 1,
                    'depends'             => array('depend#1', 'depend#2'),
                    'onFail'              => 'checkException',
                    'exceptionClass'      => '\RuntimeException',
                    'badReturnValue'      => 'emptyString',
                    'fallbackSourceId'    => 'firstFieldFallbackSourceId',
                    'preprocessor'        => array(
                        'class'  => '##this',
                        'method' => 'fooPreprocessor',
                        'args'   => array()
                    ),
                    'processor'           => array(
                        'class'  => '##this',
                        'method' => 'barProcessor',
                        'args'   => array()
                    ),
                    'preprocessors'       => array(),
                    'processors'          => array(),
                    'class'               => '\stdClass',
                    'method'              => 'someMethod',
                    'args'                => array('argument#1', 'argument#2')
                ),
                'firstField' => array(
                    'id'                  => 'firstFieldId',
                    'lazyLoading'         => 1,
                    'supplySeveralFields' => 1,
                    'depends'             => array('depend#1', 'depend#2'),
                    'onFail'              => 'checkException',
                    'exceptionClass'      => '\RuntimeException',
                    'badReturnValue'      => 'emptyString',
                    'fallbackSourceId'    => 'firstFieldFallbackSourceId',
                    'preprocessor'        => array(
                        'class'  => '##this',
                        'method' => 'fooPreprocessor',
                        'args'   => array()
                    ),
                    'processor'           => array(
                        'class'  => '##this',
                        'method' => 'barProcessor',
                        'args'   => array()
                    ),
                    'preprocessors'       => array(),
                    'processors'          => array(),
                    'class'               => '\stdClass',
                    'method'              => 'someMethod',
                    'args'                => array('argument#1', 'argument#2')
                )
            ),
            $metadata->getProviders()
        );
    }

    /**
     * @test
     */
    public function getterValidateResult()
    {
        $metadata = $this->loadMetadata('Getter');

        $this->assertInstanceOf('\Kassko\DataMapper\ClassMetadata\ClassMetadata', $metadata);
        $this->assertEquals('getterName', $metadata->getterise('firstField'));
    }

    /**
     * @test
     */
    public function setterValidateResult()
    {
        $metadata = $this->loadMetadata('Setter');

        $this->assertInstanceOf('\Kassko\DataMapper\ClassMetadata\ClassMetadata', $metadata);
        $this->assertEquals('setterName', $metadata->setterise('firstField'));
    }
}
