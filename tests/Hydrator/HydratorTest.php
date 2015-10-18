<?php
namespace Kassko\DataMapperTest\Hydrator;

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
         * For the moment, to build these metadata, we need to create a lot of resources (like Kassko\DataMapperTest\Hydrator\Fixture\DataSource\PersonDataSource) 
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
        $object = new \Kassko\DataMapperTest\Hydrator\Fixture\Model\FieldType;
        $hydrator = $this->createHydrator('Kassko\DataMapperTest\Hydrator\Fixture\Model\FieldType');
        $hydrator->hydrate($data, $object);

        $this->assertSame($expected, $object->{'get' . ucfirst($type)}());
    }

    /**
     * @test
     * @expectedException \Kassko\DataMapper\Exception\ObjectMappingException
     * @expectedExceptionCode \Kassko\DataMapper\Exception\ObjectMappingException::BAD_CONVERSION_TYPE
     */
    public function hydrateFieldTypeValidateBehaviorOnBadType() 
    {
        $data = ['someType' => '3'];
        $object = new \Kassko\DataMapperTest\Hydrator\Fixture\Model\BadFieldType;
        $hydrator = $this->createHydrator('Kassko\DataMapperTest\Hydrator\Fixture\Model\BadFieldType');
        $hydrator->hydrate($data, $object);
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
    public function hydrateFieldClassValidateResult()
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
    public function hydrateValidateFieldClassNullResult()
    {
        $propertyClass = 'Kassko\DataMapperTest\Hydrator\Fixture\Model\NestedClass';

        $hydrator = $this->createHydrator($propertyClass);
        $object = new \Kassko\DataMapperTest\Hydrator\Fixture\Model\FieldClass;
        $hydrator->hydrate([], $object);

        $this->assertNull($object->property);
    }

    /**
     * @test
     */
    public function hydrateValidateFieldClassResult()
    {
        $propertyClass = 'Kassko\DataMapperTest\Hydrator\Fixture\Model\NestedClass';

        $hydrator = $this->createHydrator($propertyClass);
        $object = new \Kassko\DataMapperTest\Hydrator\Fixture\Model\FieldClass;
        $nestedObject = new $propertyClass;

        $hydrator->hydrate(
            [
                'property' => [
                    'propertyA' => 'aaa', 
                    'propertyB' => 'bbb',
                ],
                'alreadyHydratedProperty' => $nestedObject,
                'collectionProperty' => [
                    [
                        'propertyA' => 'aaa', 
                        'propertyB' => 'bbb',
                    ],
                    [
                        'propertyA' => 'ccc', 
                        'propertyB' => 'ddd',
                    ],
                    //$nestedObject, <= Should work, fix it.
                ]
            ], 
            $object
        );
        
        $this->assertInstanceOf($propertyClass, $object->property);
        $this->assertEquals('aaa', $object->property->propertyA);
        $this->assertEquals('bbb', $object->property->propertyB);

        $this->assertSame($nestedObject, $object->alreadyHydratedProperty);

        $this->assertInternalType('array', $object->collectionProperty);
        $this->assertEquals('aaa', $object->collectionProperty[0]->propertyA);
        $this->assertEquals('bbb', $object->collectionProperty[0]->propertyB);
        $this->assertEquals('ccc', $object->collectionProperty[1]->propertyA);
        $this->assertEquals('ddd', $object->collectionProperty[1]->propertyB);
        //$this->assertInstanceOf($propertyClass, $object->collectionProperty[2]);//<= Should work, fix it.

        $this->markTestIncomplete(
          'This use case should be handled: object collection with some entries represented by raw results'
          . ' and other entries represented by already hydrated objects.'
        );
    }

    /**
     * @test
     */
    public function hydrateValidateDataSourceLoadingTriggering()
    {
        $dataSourceRealClass = 'Kassko\DataMapperTest\Hydrator\Fixture\DataSource\PersonDataSource';
        $dataSource = $this->getMockBuilder($dataSourceRealClass)
                           ->setMethods(['getData', 'getLazyLoadedData'])
                           ->getMock();
        $dataSource->expects($this->once())->method('getData');
        $dataSource->expects($this->never())->method('getLazyLoadedData');
        
        $hydrator = $this->createHydrator('Kassko\DataMapperTest\Hydrator\Fixture\Model\DataSourceLoading', [$dataSourceRealClass => $dataSource]);
        $object = new \Kassko\DataMapperTest\Hydrator\Fixture\Model\DataSourceLoading;
        
        //Should not trigger address loading (so no "getLazyLoadedData" call) since this property is configured to be lazyloaded.
        $hydrator->hydrate([], $object);
    }

    /**
     * @test
     */
    public function validateDataSourceLazyLoadingTriggering()
    {
        $dataSourceRealClass = 'Kassko\DataMapperTest\Hydrator\Fixture\DataSource\PersonDataSource';
        $dataSource = $this->getMockBuilder($dataSourceRealClass)
                           ->setMethods(['getLazyLoadedData'])
                           ->getMock();
        $dataSource->expects($this->once())->method('getLazyLoadedData');

        $classResolver = $this->createClassResolver([$dataSourceRealClass => $dataSource]);
        $this->configureObjectManager($classResolver);

        $object = new \Kassko\DataMapperTest\Hydrator\Fixture\Model\DataSourceLoading;
        //Should trigger address lazyloading (so "getLazyLoadedData" call).
        $object->getAddress();
        //Should not trigger address lazyloading for a second time (because data are already loaded).
        $object->getAddress();
    }

    /**
     * @test
     */
    public function hydrateValidateDataSourceDataLoaded()
    {
        $hydrator = $this->createHydrator('Kassko\DataMapperTest\Hydrator\Fixture\Model\DataSourceLoading');
        $object = new \Kassko\DataMapperTest\Hydrator\Fixture\Model\DataSourceLoading;
        $hydrator->hydrate([], $object);

        $this->assertEquals('name', $object->getName());
        $this->assertNull($object->address);//Check the default value of address.
        $this->assertEquals('address', $object->getAddress());//Check the lazy loaded value.
    }

    /**
     * @test
     * @todo change this test when Hydrator\ValueResolver will have its unit tests
     * and only test if methods of Hydrator\ValueResolver are called with the good arguments.
     */
    public function hydrateValidateDataSourceParametersResolution()
    {
        $dataSourceRealClass = 'Kassko\DataMapperTest\Hydrator\Fixture\DataSource\ParametersDataSource';
        $dataSource = $this->getMockBuilder($dataSourceRealClass)
                           ->setMethods(['getData'])
                           ->getMock();
        
        $hydrator = $this->createHydrator('Kassko\DataMapperTest\Hydrator\Fixture\Model\DataSourceParameters', [$dataSourceRealClass => $dataSource]);
        $object = new \Kassko\DataMapperTest\Hydrator\Fixture\Model\DataSourceParameters;

        $rawData = ['propertyA' => 'aaa'];

        $dataSource->expects($this->once())
                   ->method('getData')
                   ->with($object, $rawData, 'propertyBValue', 12, 'aaa');

        $hydrator->hydrate($rawData, $object);
    }

    /**
     * @test
     */
    public function validateDataSourceSupplySeveralFields()
    {
        $object = new \Kassko\DataMapperTest\Hydrator\Fixture\Model\DataSourceSupplySeveralFields;
        $dataSource = new \Kassko\DataMapperTest\Hydrator\Fixture\DataSource\SupplySeveralFieldsDataSource;

        $this->assertEquals($dataSource->getScalarData(), $object->getPropertyA());
        $this->assertEquals($dataSource->getArrayData(), $object->getPropertyB());

        $this->assertEquals($dataSource->getScalarDataForSeveralFields()['propertyC'], $object->getPropertyC());
        $this->assertEquals($dataSource->getScalarDataForSeveralFields()['propertyD'], $object->getPropertyD());

        $this->assertEquals($dataSource->getArrayDataForSeveralFields()['propertyE'], $object->getPropertyE());
        $this->assertEquals($dataSource->getArrayDataForSeveralFields()['propertyF'], $object->getPropertyF());
    }

    /**
     * @test
     */
    public function validateDataSourceSupplySeveralFieldsCombinedWithFieldClass()
    {
        $object = new \Kassko\DataMapperTest\Hydrator\Fixture\Model\DataSourceSupplySeveralFields;
        $dataSource = new \Kassko\DataMapperTest\Hydrator\Fixture\DataSource\SupplySeveralFieldsDataSource;

        $this->assertEquals($dataSource->getObjectData()['propertyA'], $object->getPropertyG()->propertyA);
        $this->assertEquals($dataSource->getObjectData()['propertyB'], $object->getPropertyG()->propertyB);

        $this->assertEquals($dataSource->getObjectDataForSeveralFields()['propertyH']['propertyA'], $object->getPropertyH()->propertyA);
        $this->assertEquals($dataSource->getObjectDataForSeveralFields()['propertyH']['propertyB'], $object->getPropertyH()->propertyB);
        $this->assertEquals($dataSource->getObjectDataForSeveralFields()['propertyI']['propertyA'], $object->getPropertyI()->propertyA);
        $this->assertEquals($dataSource->getObjectDataForSeveralFields()['propertyI']['propertyB'], $object->getPropertyI()->propertyB);

        $this->assertEquals($dataSource->getArrayOfObjectData()[0]['propertyA'], $object->getPropertyJ()[0]->propertyA);
        $this->assertEquals($dataSource->getArrayOfObjectData()[0]['propertyB'], $object->getPropertyJ()[0]->propertyB);
        $this->assertEquals($dataSource->getArrayOfObjectData()[1]['propertyA'], $object->getPropertyJ()[1]->propertyA);
        $this->assertEquals($dataSource->getArrayOfObjectData()[1]['propertyB'], $object->getPropertyJ()[1]->propertyB);

        $this->assertEquals($dataSource->getArrayOfObjectDataForSeveralFields()['propertyK'][0]['propertyA'], $object->getPropertyK()[0]->propertyA);
        $this->assertEquals($dataSource->getArrayOfObjectDataForSeveralFields()['propertyK'][0]['propertyB'], $object->getPropertyK()[0]->propertyB);
        $this->assertEquals($dataSource->getArrayOfObjectDataForSeveralFields()['propertyK'][1]['propertyA'], $object->getPropertyK()[1]->propertyA);
        $this->assertEquals($dataSource->getArrayOfObjectDataForSeveralFields()['propertyK'][1]['propertyB'], $object->getPropertyK()[1]->propertyB);

        $this->assertEquals($dataSource->getArrayOfObjectDataForSeveralFields()['propertyL'][0]['propertyA'], $object->getPropertyL()[0]->propertyA);
        $this->assertEquals($dataSource->getArrayOfObjectDataForSeveralFields()['propertyL'][0]['propertyB'], $object->getPropertyL()[0]->propertyB);
        $this->assertEquals($dataSource->getArrayOfObjectDataForSeveralFields()['propertyL'][1]['propertyA'], $object->getPropertyL()[1]->propertyA);
        $this->assertEquals($dataSource->getArrayOfObjectDataForSeveralFields()['propertyL'][1]['propertyB'], $object->getPropertyL()[1]->propertyB);
    }

    /**
     * @test
     */
    public function hydrateValidateDataSourceProcessorTriggering()
    {
        /**
         * We cannot fetch the metadata from the mock object class name
         * So we use the real object class name.
         */

        $processorRealClassName = 'Kassko\DataMapperTest\Hydrator\Fixture\Model\Processor';
        $processorMockClassName = 'ProcessorMock';
        $object = $this->getMockBuilder($processorRealClassName)
                       ->setMockClassName($processorMockClassName)
                       ->setMethods(['somePreprocessor', 'someProcessor'])
                       ->getMock();

        $object->expects($this->once())
               ->method('somePreprocessor');
        $object->expects($this->once())
               ->method('someProcessor');

        $classMetadata = $this->objectManager->getMetadata($processorRealClassName);
        $cmFactory = $this->getMockBuilder(ClassMetadataFactory::class)->getMock();
        $cmFactory->method('loadMetadata')
                  ->willReturn($classMetadata);
        $this->objectManager->setClassMetadataFactory($cmFactory);

        $hydrator = $this->createHydrator($processorMockClassName);
        $hydrator->hydrate([], $object);
    }

    /**
     * @test
     */
    public function hydrateValidateDataSourceProcessorsTriggering()
    {
        /**
         * We cannot fetch the metadata from the mock object class name
         * So we use the real object class name.
         */

        $objectRealClass = 'Kassko\DataMapperTest\Hydrator\Fixture\Model\Processors';
        $objectMockClass = 'ProcessorsMock';
        $object = $this->getMockBuilder($objectRealClass)
                       ->setMockClassName($objectMockClass)
                       ->setMethods(['somePreprocessorA', 'somePreprocessorB', 'someProcessorA', 'someProcessorB'])
                       ->getMock();

        $object->expects($this->once())
               ->method('somePreprocessorA');
        $object->expects($this->once())
               ->method('somePreprocessorB');
        $object->expects($this->once())
               ->method('someProcessorA');
        $object->expects($this->once())
               ->method('someProcessorB');

        $processorRealClass = 'Kassko\DataMapperTest\Hydrator\Fixture\Processor\SomeProcessor';
        $processor = $this->getMockBuilder($processorRealClass)
                          ->setMethods(['process'])
                          ->getMock();
        $processor->expects($this->exactly(2))
                  ->method('process');

        $classMetadata = $this->objectManager->getMetadata($objectRealClass);
        $cmFactory = $this->getMockBuilder(ClassMetadataFactory::class)->getMock();
        $cmFactory->method('loadMetadata')
                  ->willReturn($classMetadata);
        $this->objectManager->setClassMetadataFactory($cmFactory);

        $hydrator = $this->createHydrator($objectMockClass, [$processorRealClass => $processor]);
        $hydrator->hydrate([], $object);
    }

    /**
     * @test
     *
     * Test if the fallback data source is used if the main data source is not stable (throws an Exception).
     */
    public function hydrateValidateDataSourceExceptionOnFail_Call()
    {
        $objectClass = 'Kassko\DataMapperTest\Hydrator\Fixture\Model\DataSourceExceptionOnFail';
        $dataSourceRealClass = 'Kassko\DataMapperTest\Hydrator\Fixture\DataSource\SomeDataSource';
        $fallbackDataSourceClass = 'Kassko\DataMapperTest\Hydrator\Fixture\DataSource\FallbackDataSource';

        $dataSource = $this->getMockBuilder($dataSourceRealClass)
                           ->setMethods(['getData'])
                           ->getMock();
        $dataSource->method('getData')->will($this->throwException(new \RuntimeException));

        $fallbackDataSourceMock = $this->getMockBuilder($fallbackDataSourceClass)
                                       ->setMethods(['getData'])
                                       ->getMock();
        $fallbackDataSourceMock->expects($this->once())
                               ->method('getData');

        $hydrator = $this->createHydrator($objectClass, [$dataSourceRealClass => $dataSource, $fallbackDataSourceClass => $fallbackDataSourceMock]);
        $object = new $objectClass;
        $hydrator->hydrate([], $object);
    }

    /**
     * @test
     *
     * Test if object is hydrated from the fallback data source if the main one is not stable (throws an Exception).
     */
    public function hydrateValidateDataSourceExceptionOnFail_Result()
    {
        $objectClass = 'Kassko\DataMapperTest\Hydrator\Fixture\Model\DataSourceExceptionOnFail';
        $dataSourceRealClass = 'Kassko\DataMapperTest\Hydrator\Fixture\DataSource\SomeDataSource';
        $fallbackDataSourceClass = 'Kassko\DataMapperTest\Hydrator\Fixture\DataSource\FallbackDataSource';

        $dataSource = $this->getMockBuilder($dataSourceRealClass)
                           ->setMethods(['getData'])
                           ->getMock();
        $dataSource->method('getData')->will($this->throwException(new \RuntimeException));

        $hydrator = $this->createHydrator($objectClass, [$dataSourceRealClass => $dataSource]);
        $object = new $objectClass;
        $hydrator->hydrate([], $object);

        $fallbackDataSource = new $fallbackDataSourceClass;
        $this->assertEquals($fallbackDataSource->getData(), $object->property);
    }

    /**
     * @test
     *
     * Test if the fallback data source is not used if the main data source is stable (doesn't throw an Exception).
     */
    public function hydrateValidateDataSourceExceptionOnFail_NoCall()
    {
        $objectClass = 'Kassko\DataMapperTest\Hydrator\Fixture\Model\DataSourceExceptionOnFail';
        $fallbackDataSourceClass = 'Kassko\DataMapperTest\Hydrator\Fixture\DataSource\FallbackDataSource';

        $fallbackDataSourceMock = $this->getMockBuilder($fallbackDataSourceClass)
                                       ->setMethods(['getData'])
                                       ->getMock();
        $fallbackDataSourceMock->expects($this->never())
                               ->method('getData');

        $hydrator = $this->createHydrator($objectClass, [$fallbackDataSourceClass => $fallbackDataSourceMock]);
        $object = new $objectClass;
        $hydrator->hydrate([], $object);
    }

    /**
     * @test
     * @dataProvider dataSourceBadReturnValueProvider
     *
     * Test if fallback data source is called on bad return value.
     */
    public function hydrateValidateCallDataSourceBadReturnValueOnFail($objectClass, $dataSourceClass, $returnValue, $isBadReturnValue)
    {
        $dataSourceRealClass = 'Kassko\DataMapperTest\Hydrator\Fixture\DataSource\SomeDataSource';
        $fallbackDataSourceClass = 'Kassko\DataMapperTest\Hydrator\Fixture\DataSource\FallbackDataSource';

        $dataSourceMock = $this->getMockBuilder($dataSourceRealClass)
                               ->setMockClassName($dataSourceClass)
                               ->setMethods(['getData'])
                               ->getMock();
        $dataSourceMock->method('getData')->willReturn($returnValue);

        $fallbackDataSourceMock = $this->getMockBuilder($fallbackDataSourceClass)
                                       ->setMethods(['getData'])
                                       ->getMock();
        if ($isBadReturnValue) {
            $fallbackDataSourceMock->expects($this->once())
                                   ->method('getData');
        } else {
            $fallbackDataSourceMock->expects($this->never())
                                   ->method('getData');
        }
        
        $hydrator = $this->createHydrator(
            $objectClass, 
            [$dataSourceClass => $dataSourceMock, $fallbackDataSourceClass => $fallbackDataSourceMock]
        );
        $object = new $objectClass;
        $hydrator->hydrate([], $object);
    }

    /**
     * @test
     * @dataProvider dataSourceBadReturnValueProvider
     *
     * Test if object is hydrated from a fallback data source.
     */
    public function hydrateValidateResultDataSourceBadReturnValueOnFail($objectClass, $dataSourceClass, $returnValue, $isBadReturnValue)
    {
        $expectedReturnValue = '123';

        $dataSourceRealClass = 'Kassko\DataMapperTest\Hydrator\Fixture\DataSource\SomeDataSource';
        $fallbackDataSourceClass = 'Kassko\DataMapperTest\Hydrator\Fixture\DataSource\FallbackDataSource';

        $dataSourceMock = $this->getMockBuilder($dataSourceRealClass)
                               ->setMockClassName($dataSourceClass)
                               ->setMethods(['getData'])
                               ->getMock();
        $dataSourceMock->method('getData')->willReturn($returnValue);

        $fallbackDataSourceMock = $this->getMockBuilder($fallbackDataSourceClass)
                                       ->setMethods(['getData'])
                                       ->getMock();
        $fallbackDataSourceMock->method('getData')->willReturn($expectedReturnValue);
        
        $hydrator = $this->createHydrator($objectClass, [$dataSourceClass => $dataSourceMock, $fallbackDataSourceClass => $fallbackDataSourceMock]);
        $object = new $objectClass;
        $hydrator->hydrate([], $object);

        $this->assertEquals($expectedReturnValue, $object->property);
    }

    public function dataSourceBadReturnValueProvider()
    {
        return [
            //Bad return values.
            ['Kassko\DataMapperTest\Hydrator\Fixture\Model\DataSourceNullReturnValueOnFail', 'NullSource', null, true], 
            ['Kassko\DataMapperTest\Hydrator\Fixture\Model\DataSourceFalseReturnValueOnFail', 'FalseSource', false, true], 
            ['Kassko\DataMapperTest\Hydrator\Fixture\Model\DataSourceEmptyStringReturnValueOnFail', 'EmptyStringSource', '', true], 
            ['Kassko\DataMapperTest\Hydrator\Fixture\Model\DataSourceEmptyArrayReturnValueOnFail', 'EmptyArraySource', [], true], 

            //Good return values.
            ['Kassko\DataMapperTest\Hydrator\Fixture\Model\DataSourceNullReturnValueOnFail', 'NullSource', '123', false], 
            ['Kassko\DataMapperTest\Hydrator\Fixture\Model\DataSourceFalseReturnValueOnFail', 'FalseSource', '123', false], 
            ['Kassko\DataMapperTest\Hydrator\Fixture\Model\DataSourceEmptyStringReturnValueOnFail', 'EmptyStringSource', '123', false], 
            ['Kassko\DataMapperTest\Hydrator\Fixture\Model\DataSourceEmptyArrayReturnValueOnFail', 'EmptyArraySource', '123', false], 
        ];
    }

    /**
     * @test
     *
     * Test if a dependant source is called in addition to the main source.
     */
    public function hydrateValidateNumberCallsDataSourceDepends()
    {
        $dependantDataSourceClass = 'Kassko\DataMapperTest\Hydrator\Fixture\DataSource\DependantDataSource';
        $dependencyDataSourceClass = 'Kassko\DataMapperTest\Hydrator\Fixture\DataSource\DependencyDataSource';

        $dependantDataSourceMock = $this->getMockBuilder($dependantDataSourceClass)
                               ->setMethods(['getData'])
                               ->getMock();
        $dependantDataSourceMock->expects($this->once())
                       ->method('getData');

        $dependencyDataSourceMock = $this->getMockBuilder($dependencyDataSourceClass)
                               ->setMethods(['getData'])
                               ->getMock();
        $dependencyDataSourceMock->expects($this->once())
                       ->method('getData');

        $classResolver = $this->createClassResolver([$dependantDataSourceClass => $dependantDataSourceMock, $dependencyDataSourceClass => $dependencyDataSourceMock]);
        $this->configureObjectManager($classResolver);

        $object = new \Kassko\DataMapperTest\Hydrator\Fixture\Model\DataSourceDepends;
        $object->getPropertyB();

        /**
         * The data source of propertyA (DependencyDataSource) should be called in addition to 
         * the data source of the propertyB. 
         */
    }

    /**
     * @test
     *
     * Test if the dependency source is the first source called.
     */
    public function hydrateValidateOrderCallsDataSourceDepends()
    {
        $dependencyDataSourceClass = 'Kassko\DataMapperTest\Hydrator\Fixture\DataSource\DependencyDataSource';

        $dependencyDataSourceMock = $this->getMockBuilder($dependencyDataSourceClass)
                               ->setMethods(['getData'])
                               ->getMock();
        $dependencyDataSourceMock->expects($this->once())
                       ->method('getData');

        $classResolver = $this->createClassResolver([$dependencyDataSourceClass => $dependencyDataSourceMock]);
        $this->configureObjectManager($classResolver);

        $object = new \Kassko\DataMapperTest\Hydrator\Fixture\Model\DataSourceDepends;
        $object->getPropertyB();

        /**
         * The data source of propertyA (DependencyDataSource) should be called before the data source of the propertyB (DependantDataSource).
         * So we check if DependencyDataSource is the first source called 
         * because propertyB is dependant of propertyA. 
         */
    }

    /**
     * @test
     *
     * Test if the dependency source is the first source called.
     */
    public function hydrateValidateResultDataSourceDepends()
    {
        $object = new \Kassko\DataMapperTest\Hydrator\Fixture\Model\DataSourceDepends;
        $object->getPropertyB();

        $dataSource = new \Kassko\DataMapperTest\Hydrator\Fixture\DataSource\DependencyDataSource;
        $this->assertEquals($dataSource->getData(), $object->propertyA);

        /**
         * The data source of propertyA (DependencyDataSource) should be called before the data source of the propertyB (DependantDataSource).
         * So we check if DependencyDataSource is the first source called 
         * because propertyB is dependant of propertyA. 
         */
    }

    /**
     * @test
     *
     * Test DataSource arguments resolution when DataSource is shared between several instances.
     * This case occurs when we works with collection.  
     * We make sure that the resolution is done for each instance of the collection and not only once.
     */
    public function collectionOfObjectsWithDataSourceArgsToResolveValidateBehaviour()
    {
        $dataSourceClass = 'DataSourceMock';

        $dataSourceMock = $this->getMockBuilder($dataSourceClass)
                               ->setMethods(['getData'])
                               ->getMock();
        $dataSourceMock->expects($this->exactly(2))
                       ->method('getData')
                       ->withConsecutive(
                            array($this->equalTo('value a')),
                            array($this->equalTo('value b'))
                        );

        $classResolver = $this->createClassResolver([$dataSourceClass => $dataSourceMock]);
        $this->configureObjectManager($classResolver);

        $object = new \Kassko\DataMapperTest\Hydrator\Fixture\Model\CaseCollectionOfObjectsWithDataSourceArgsToResolve();

        $items = $object->getItems();
        $items[0]->getPropertyB();
        $items[1]->getPropertyB();
    }

    /**
     * @return Hydrator
     */
    private function createHydrator($objectClass, array $map = [])
    {
        $classResolver = $this->createClassResolver($map);
        $this->configureObjectManager($classResolver);

        $hydrator = $this->objectManager->createHydratorFor($objectClass);
        $this->configureHydrator($classResolver);

        return $hydrator;
    }

    private function createClassResolver(array $map = [])
    {
        $classResolver = null;

        if (count($map)) {        
            $classResolver = new CallableClassResolver(
                function ($class) use ($map) {
                    if (isset($map[$class])) {
                        return $map[$class];
                    }
                    return new $class;
                }
            );
        } 

        return $classResolver;
    }

    private function configureObjectManager(CallableClassResolver $classResolver = null)
    {
        if (isset($classResolver)) {        
            $this->objectManager->setClassResolver($classResolver);
        } else {
            $this->objectManager->unsetClassResolver(); 
        }
    }

    private function configureHydrator(CallableClassResolver $classResolver = null)
    {
        if (isset($classResolver)) {        
            $this->objectManager->setClassResolver($classResolver);
        } else {
            $this->objectManager->unsetClassResolver(); 
        }
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
        ->unsetCacheProfile()//Useful to really call data source and to expect a number of calls.
        ;
    }

    private function registerLazyLoader()
    {
        $lazyLoaderFactory = new LazyLoaderFactory($this->objectManager);
        Registry::getInstance()[Registry::KEY_LAZY_LOADER_FACTORY] = $lazyLoaderFactory;
    }
}
