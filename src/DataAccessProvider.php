<?php

namespace Kassko\DataAccess;

use Closure;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Cache\ArrayCache;
use Kassko\ClassResolver\ClosureClassResolver;
use Kassko\ClassResolver\FactoryClassResolver;
use Kassko\DataAccess\ClassMetadataLoader\AnnotationLoader;
use Kassko\DataAccess\ClassMetadataLoader\DelegatingLoader;
use Kassko\DataAccess\ClassMetadataLoader\LoaderResolver;
use Kassko\DataAccess\ClassMetadata\ClassMetadataFactory;
use Kassko\DataAccess\Configuration\CacheConfiguration;
use Kassko\DataAccess\Configuration\ClassMetadataFactoryConfigurator;
use Kassko\DataAccess\Configuration\Configuration;
use Kassko\DataAccess\Listener\ObjectListenerResolverChain;
use Kassko\DataAccess\Registry\Registry;
use Psr\Log\LoggerInterface;

/**
* Construct and provide main objects of DataAccess with a default configuration.
*
* @author kko
*/
class DataAccessProvider
{
    private $classResolver;
    private $objectListenerResolver;
    private $logger;
    private $cache = [];

    public function getResultBuilderFactory()
    {
        return $this->getCache(
            'result_builder_factory',
            function () {

                $objectManager = $this->getObjectManager();

                //LazyLoaderFactory
                $lazyLoaderFactory = new LazyLoaderFactory($objectManager);
                Registry::getInstance()->setLazyLoaderFactory($lazyLoaderFactory);

                //Logger
                if (isset($this->logger)) {
                    Registry::getInstance()->setLogger($this->logger);
                }

                return new ResultBuilderFactory($objectManager);
            }
        );
    }

    public function setClassResolver(Closure $classResolver)
    {
        $this->classResolver = $classResolver;
        return $this;
    }

    public function setObjectListenerResolver(Closure $objectListenerResolver)
    {
        $this->objectListenerResolver = $objectListenerResolver;
        return $this;
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    private function getObjectManager()
    {
        return $this->getCache(
            'object_manager',
            function () {

                //Configuration
                $configuration = (new Configuration)
                    ->setClassMetadataCacheConfig(new CacheConfiguration(new ArrayCache))
                    ->setResultCacheConfig(new CacheConfiguration(new ArrayCache))
                ;

                //ClassMetadataFactory
                $delegatingLoader = new DelegatingLoader(
                    new LoaderResolver(
                        new AnnotationLoader(
                            new Reader
                        )
                    )
                );
                $cmFactory = (new ClassMetadataFactory)->setClassMetadataLoader($delegatingLoader);
                $cmConfigurator = new ClassMetadataFactoryConfigurator($configuration);
                $cmConfigurator->configure($cmFactory);

                //ClassResolver
                if (isset($this->classResolver)) {
                    $classResolver = new ClosureClassResolver($this->classResolver);
                }

                //ObjectListenerResolver
                if (isset($this->objectListenerResolver)) {
                    $olr = new ClosureObjectListenerResolver($this->objectListenerResolver);
                }

                //ObjectManager
                $objectManager = (new ObjectManager())
                    ->setConfiguration($configuration)
                    ->setClassMetadataFactory($cmFactory)
                    ->setObjectListenerResolver($olr)
                    ->setClassResolver($classResolver)
                ;

                return $objectManager;
            }
        );
    }

    private function getCache($key, Closure $getter)
    {
        if (! isset($this->cache[$key])) {
            $this->cache[$key] = $getter();
        }

        return $this->cache[$key];
    }
}