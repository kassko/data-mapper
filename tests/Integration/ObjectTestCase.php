<?php

namespace Kassko\DataMapperTest\Integration;

use Closure;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
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

class ObjectTestCase extends \PHPUnit_Framework_TestCase
{
    protected function init()
    {
        AnnotationRegistry::registerLoader('class_exists');

        $this->createObjectManager();
        $this->registerLazyLoader();
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
