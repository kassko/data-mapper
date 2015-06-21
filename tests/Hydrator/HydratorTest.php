<?php
namespace Kassko\DataMapperTest\ClassMetadata;

use Closure;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Kassko\ClassResolver\CallableClassResolver;
use Kassko\DataMapper\Cache\ArrayCache;
use Kassko\DataMapper\ClassMetadata;
use Kassko\DataMapper\ClassMetadataLoader\AnnotationLoader;
use Kassko\DataMapper\ClassMetadataLoader\DelegatingLoader;
use Kassko\DataMapper\ClassMetadataLoader\LoaderResolver;
use Kassko\DataMapper\ClassMetadata\ClassMetadataFactory;
use Kassko\DataMapper\Configuration\CacheConfiguration;
use Kassko\DataMapper\Configuration\ClassMetadataFactoryConfigurator;
use Kassko\DataMapper\Configuration\Configuration;
use Kassko\DataMapper\Hydrator\Hydrator;
use Kassko\DataMapper\LazyLoader\LazyLoaderFactory;
use Kassko\DataMapper\MethodInvoker\MagicMethodInvoker;
use Kassko\DataMapper\ObjectManager;
use Kassko\DataMapper\Registry\Registry;

/**
 * Class Hydrator
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
    public static function setUpBeforeClass()
    {
        AnnotationRegistry::reset();
        AnnotationRegistry::registerLoader('class_exists');
    }

    /**
     * @return void
     */
    public function setUp()
    {
        /**
         * @todo: when it will be ready, use directly the ClassMetadataBuilder. All loaders will use it to create the metadata.
         *
         * For the moment, to build these metadata, we need to create a lot of resources (like Kassko\DataMapperTest\Hydrator\Fixture\PersonDataSource) 
         * and to use a resource loader (like AnnotationLoader).
         */

        $loader = new DelegatingLoader(new LoaderResolver([new AnnotationLoader(new AnnotationReader)]));
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

        $lazyLoaderFactory = new LazyLoaderFactory($this->objectManager);
        Registry::getInstance()[Registry::KEY_LAZY_LOADER_FACTORY] = $lazyLoaderFactory;
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

    public function hydrateValidateNestedObject()
    {
    }

    /**
     * @test
     */
    public function hydrateValidateDataSourceLoadingTriggering()
    {
        $dataSourceOriginalClass = 'Kassko\DataMapperTest\Hydrator\Fixture\PersonDataSource';
        $dataSource = $this->getMockBuilder($dataSourceOriginalClass)
                           ->setMethods(['getData'])
                           ->getMock()
        ;
        $dataSource->expects($this->once())->method('getData');
        
        $hydrator = $this->createHydrator('Kassko\DataMapperTest\Hydrator\Fixture\Model\PersonWithDataSources', $dataSource, $dataSourceOriginalClass);
        $person = new \Kassko\DataMapperTest\Hydrator\Fixture\Model\PersonWithDataSources;
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
        $dataSource->expects($this->never())->method('getLazyLoadedData');
        
        $hydrator = $this->createHydrator('Kassko\DataMapperTest\Hydrator\Fixture\Model\PersonWithDataSources', $dataSource, $dataSourceOriginalClass);
        $person = new \Kassko\DataMapperTest\Hydrator\Fixture\Model\PersonWithDataSources;
        $hydrator->hydrate([], $person);
    }

    /**
     * @test
     */
    public function hydrateValidateDataSourceDataLoaded()
    {        
        $hydrator = $this->createHydrator('Kassko\DataMapperTest\Hydrator\Fixture\Model\PersonWithDataSources');
        $person = new \Kassko\DataMapperTest\Hydrator\Fixture\Model\PersonWithDataSources;
        $hydrator->hydrate([], $person);

        $this->assertEquals('name', $person->getName());
        $this->assertEquals('address', $person->getAddress());//Trigger lazy loading.
    }

    /**
     * @return Hydrator
     */
    private function createHydrator($objectClass, $dataSource = null, $dataSourceOriginalClass = null)
    {
        $classResolver = new CallableClassResolver(
            function ($class) use ($dataSource, $dataSourceOriginalClass) {
                if (null !== $dataSource && $dataSourceOriginalClass === $class) {
                    return $dataSource;
                }
                return new $class;
            }
        );

        return 
        $this->objectManager
        ->setClassResolver($classResolver)
        ->createHydratorFor($objectClass)
        ;
    }
}
