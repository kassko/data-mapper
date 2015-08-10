<?php
namespace Kassko\DataMapperTest\ClassMetadata;

use Closure;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Kassko\ClassResolver\CallableClassResolver;
use Kassko\DataMapper\Cache\ArrayCache;
use Kassko\DataMapper\ClassMetadata;
use Kassko\DataMapper\ClassMetadataLoader\AnnotationLoader;
use Kassko\DataMapper\ClassMetadata\ClassMetadataFactory;
use Kassko\DataMapper\Configuration\CacheConfiguration;
use Kassko\DataMapper\Configuration\ClassMetadataFactoryConfigurator;
use Kassko\DataMapper\Configuration\Configuration;
use Kassko\DataMapper\Exception\ObjectMappingException;
use Kassko\DataMapper\Hydrator\Hydrator;
use Kassko\DataMapper\LazyLoader\LazyLoaderFactory;
use Kassko\DataMapper\MethodInvoker\MagicMethodInvoker;
use Kassko\DataMapper\ObjectManager;
use Kassko\DataMapper\Registry\Registry;

/**
 * Class HydratorTest
 * 
 * @author kko
 */
class HydratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @return void
     */
    public function setUp()
    {
        AnnotationRegistry::registerLoader('class_exists');

        /**
         * @todo: when available, use directly the ClassMetadataBuilder. All loaders will use it to create the metadata at a high level.
         *
         * For the moment, to build these metadata, we need to create a lot of resources (like Kassko\DataMapperTest\Hydrator\Fixture\PersonDataSource) 
         * and to use a resource loader (like AnnotationLoader).
         */
        
        $this->createObjectManager();
        $this->registerLazyLoader();
    }

    /**
     * @test
     */
    public function hydrateValidate()
    {
        $data = ['name' => 'jackson', 'address' => '3 street of bars'];
        $person = new \Kassko\DataMapperTest\Hydrator\Fixture\Model\Person;
        $hydrator = $this->createHydrator('Kassko\DataMapperTest\Hydrator\Fixture\Model\Person');
        $hydrator->hydrate($data, $person);

        $this->assertEquals('jackson', $person->getName());
        $this->assertEquals('3 street of bars', $person->getAddress());
    }

    /**
     * @test
     * @dataProvider hydrateFieldTypeProvider
     */
    public function hydrateFieldTypeValidateResult(
        $type,
        $expected, 
        $got
    ) {
        $data = [$type => $got];
        $fieldType = new \Kassko\DataMapperTest\Hydrator\Fixture\Model\FieldType;
        $hydrator = $this->createHydrator('Kassko\DataMapperTest\Hydrator\Fixture\Model\FieldType');
        $hydrator->hydrate($data, $fieldType);

        $this->assertSame($expected, $fieldType->{'get' . ucfirst($type)}());
    }

    /**
     * @test
     * @expectedException \Kassko\DataMapper\Exception\ObjectMappingException
     * @expectedExceptionCode \Kassko\DataMapper\Exception\ObjectMappingException::BAD_CONVERSION_TYPE
     */
    public function hydrateFieldTypeValidateBehaviorOnBadType() 
    {
        $data = ['someType' => '3'];
        $fieldType = new \Kassko\DataMapperTest\Hydrator\Fixture\Model\BadFieldType;
        $hydrator = $this->createHydrator('Kassko\DataMapperTest\Hydrator\Fixture\Model\BadFieldType');
        $hydrator->hydrate($data, $fieldType);
    }

    /**
     * @return array
     */
    public function hydrateFieldTypeProvider()
    {
        return [
            ['someBool', true, true],
            ['someBool', false, false],
            ['someBool', true, 1],
            ['someBool', false, 0],

            ['someInt', 3, 3],
            ['someInt', 3, 3.0],
            ['someInt', 3, '3'],
            ['someInt', 3, '3.0'],

            ['someFloat', 3.0, 3.0],
            ['someFloat', 3.0, 3],
            ['someFloat', 3.0, '3.0'],
            ['someFloat', 3.0, '3'],

            ['someString', 'some string', 'some string'],
            ['someString', '3', 3],
            ['someString', '3', 3.0],
            ['someString', 'true', 'true'],

            ['someArray', [3], [3]],//If property is an array, it's value will not be converted.
        ];
    }

    /**
     * @test
     */
    public function hydrateDeepValidateResult()
    {
        $street = '3 street of bars';
        $postalCode = '54000';

        $data = ['address' => ['street' => $street, 'postalCode' => $postalCode]];
        $person = new \Kassko\DataMapperTest\Hydrator\Fixture\Model\PersonB;
        $hydrator = $this->createHydrator('Kassko\DataMapperTest\Hydrator\Fixture\Model\PersonB');
        $hydrator->hydrate($data, $person);

        $this->assertEquals($street, $person->address->street);
        $this->assertEquals($postalCode, $person->address->postalCode);
    }

    /**
     * @test
     */
    public function hydrateValidateDataSourceLoadingTriggering()
    {
        $dataSourceOriginalClass = 'Kassko\DataMapperTest\Hydrator\Fixture\PersonDataSource';
        $dataSource = $this->getMockBuilder($dataSourceOriginalClass)
                           ->setMethods(['getData', 'getLazyLoadedData'])
                           ->getMock()
        ;
        $dataSource->expects($this->once())->method('getData');
        $dataSource->expects($this->never())->method('getLazyLoadedData');
        
        $hydrator = $this->createHydrator('Kassko\DataMapperTest\Hydrator\Fixture\Model\DataSourcesLoading', $dataSource, $dataSourceOriginalClass);
        $person = new \Kassko\DataMapperTest\Hydrator\Fixture\Model\DataSourcesLoading;
        
        //Should not trigger address loading (so no "getLazyLoadedData" call) since this property is configured to be lazyloaded.
        $hydrator->hydrate([], $person);
    }

    /**
     * @test
     */
    public function hydrateValidateDataSourceLazyLoadingTriggering()
    {
        $dataSourceOriginalClass = 'Kassko\DataMapperTest\Hydrator\Fixture\PersonDataSource';
        $dataSource = $this->getMockBuilder($dataSourceOriginalClass)
                           ->setMethods(['getLazyLoadedData'])
                           ->getMock()
        ;
        $dataSource->expects($this->once())->method('getLazyLoadedData');
        
        $hydrator = $this->createHydrator('Kassko\DataMapperTest\Hydrator\Fixture\Model\DataSourcesLoading', $dataSource, $dataSourceOriginalClass);
        $person = new \Kassko\DataMapperTest\Hydrator\Fixture\Model\DataSourcesLoading;

        $hydrator->hydrate([], $person);

        //Should trigger address lazyloading (so "getLazyLoadedData" call).
        $person->getAddress();

        //Should not trigger address lazyloading for a second time (because data are already loaded).
        $person->getAddress();
    }

    /**
     * @test
     */
    public function hydrateValidateDataSourceDataLoaded()
    {
        $hydrator = $this->createHydrator('Kassko\DataMapperTest\Hydrator\Fixture\Model\DataSourcesLoading');
        $person = new \Kassko\DataMapperTest\Hydrator\Fixture\Model\DataSourcesLoading;
        $hydrator->hydrate([], $person);

        $this->assertEquals('name', $person->getName());
        $this->assertNull($person->address);//Check the default value of address.
        $this->assertEquals('address', $person->getAddress());//Check the lazy loaded value.
    }

    public function hydrateValidateAdvancedNestedObject()
    {//array of objects
    }

    public function hydrateValidateSupplySeveralFields()
    {
    }

    public function hydrateValidateParametersResolution()
    {
    }

    public function hydrateValidateProcessorsTriggering()
    {
    }

    public function hydrateValidateOnFail()
    {
    }

    public function hydrateValidateExeptionClass()
    {
    }

    public function hydrateValidateBadReturnValue()
    {
    }

    public function hydrateValidateFallbackSources()
    {
    }

    public function hydrateValidateDepends()
    {
    }

    public function hydrateValidateClassResolution()
    {
    }

    public function hydrateValidateExpressionLanguageMethods()
    {
    }

    public function hydrateValidateSmartFieldDefaultValue()
    {
    }

    public function hydrateValidateConfigurationVariables()
    {
    }

    /**
     * @return Hydrator
     */
    private function createHydrator($objectClass, $dataSource = null, $dataSourceOriginalClass = null)
    {
        if (null !== $dataSource && null !== $dataSourceOriginalClass) {
            $classResolver = new CallableClassResolver(
                function ($class) use ($dataSource, $dataSourceOriginalClass) {
                    if ($dataSourceOriginalClass === $class) {
                        return $dataSource;
                    }
                    return new $class;
                }
            );

            $this->objectManager->setClassResolver($classResolver);
        }

        return $this->objectManager->createHydratorFor($objectClass);
    }

    private function createObjectManager()
    {
        $loader = new AnnotationLoader(new AnnotationReader);
        $cmFactory = (new ClassMetadataFactory)->setClassMetadataLoader($loader);
        $configuration = (new Configuration)
        ->setDefaultClassMetadataResourceType('annotations')
        ->setClassMetadataCacheConfig((new CacheConfiguration)->setCache(new ArrayCache))
        ->setResultCacheConfig((new CacheConfiguration)->setCache(new ArrayCache))
        ;

        $cmConfigurator = new ClassMetadataFactoryConfigurator($configuration);
        $cmConfigurator->configure($cmFactory);

        $this->objectManager = ObjectManager::getInstance()
        ->setConfiguration($configuration)
        ->setClassMetadataFactory($cmFactory)
        ->setMethodInvoker(new MagicMethodInvoker)
        ;
    }

    private function registerLazyLoader()
    {
        $lazyLoaderFactory = new LazyLoaderFactory($this->objectManager);
        Registry::getInstance()[Registry::KEY_LAZY_LOADER_FACTORY] = $lazyLoaderFactory;
    }
}
