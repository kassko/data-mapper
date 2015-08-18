<?php

namespace Kassko\DataMapper;

use Kassko\ClassResolver\ClassResolverInterface;
use Kassko\DataMapper\Cache\ArrayCache;
use Kassko\DataMapper\Cache\CacheProfile;
use Kassko\DataMapper\ClassMetadataLoader\LoadingCriteria;
use Kassko\DataMapper\ClassMetadata\ClassMetadataFactoryInterface;
use Kassko\DataMapper\ClassMetadata\Model\Source;
use Kassko\DataMapper\Configuration\Configuration;
use Kassko\DataMapper\Configuration\ObjectKey;
use Kassko\DataMapper\Exception\ObjectMappingException;
use Kassko\DataMapper\Expression\ExpressionContext;
use Kassko\DataMapper\Hydrator;
use Kassko\DataMapper\Hydrator\ExpressionLanguageEvaluator;
use Kassko\DataMapper\Hydrator\HydrationStrategy\ClosureHydrationStrategy;
use Kassko\DataMapper\Hydrator\HydrationStrategy\DateHydrationStrategy;
use Kassko\DataMapper\LazyLoader\LazyLoaderFactoryInterface;
use Kassko\DataMapper\Listener\Events;
use Kassko\DataMapper\Listener\ObjectListenerResolverInterface;
use Kassko\DataMapper\MethodInvoker\MethodInvoker;
use Kassko\DataMapper\Query\CacheConfig;
use Kassko\DataMapper\Query\ResultManager;
use Psr\Log\LoggerInterface;

/**
* Manage persistent object.
*
* @author kko
*/
class ObjectManager
{
    private $classMetadataFactory;
    private $configuration;
    private $resultManager;
    private $objectListenerResolver;
    private $classResolver;
    private $lazyLoaderFactory;
    private $logger;
    /**
     * @var ExpressionLanguage
     */
    private $expressionLanguageEvaluator;
    /**
     * Contains all the expression context variables.
     * @var ExpressionContext
     */
    private $expressionContext;
    private $hydratorInstances = [];
    /**
     * @var array
     */
    private $identityMap = [];
    /**
     * @var MethodInvoker
     */
    private $methodInvoker;
    /**
     * @var CacheProfile
     */
    private $cacheProfile;

    private static $eventToRegisterData = [
        Events::OBJECT_PRE_CREATE => 'preCreate',
        Events::OBJECT_PRE_UPDATE => 'preUpdate',
        Events::OBJECT_PRE_DELETE => 'preDelete',

        Events::OBJECT_POST_CREATE => 'postCreate',
        Events::OBJECT_POST_UPDATE => 'postUpdate',
        Events::OBJECT_POST_DELETE => 'postDelete',
        Events::OBJECT_POST_LOAD => 'postLoad',
        Events::OBJECT_POST_LOAD_LIST => 'postLoadList',
    ];

    protected function __construct()
    {
        $this->resultManager = new ResultManager($this);
        $this->cacheProfile = new CacheProfile(new ArrayCache);
    }

    /**
     * Factory method to create ObjectManager instances.
     *
     */
    public static function getInstance()
    {
        return new static;
    }

    public function checkIfIsLoadable($object)
    {
        $traitsUses = $this->getClassUsesDeeply(get_class($object));

        if (
            ! in_array('Kassko\\DataMapper\\ObjectExtension\\LoadableTrait', $traitsUses)
            &&
            ! in_array('Kassko\\DataMapper\\ObjectExtension\\LazyLoadableTrait', $traitsUses)
        ) {
            throw new ObjectMappingException(
                sprintf(
                    'To work with DataSource, the class "%s" must use the trait "Kassko\\DataMapper\\ObjectExtension\\LoadableTrait"'
                    . ' or "Kassko\\DataMapper\\ObjectExtension\\LazyLoadableTrait"', 
                    get_class($object)
                ) 
            );
        }
    }

    private function getClassUsesDeeply($class, $autoload = true)
    {
        /**
         * @todo Add a cache with all traits uses for each classes.
         */

        $traits = [];

        // Get traits of all parent classes
        do {
            $traits = array_merge(class_uses($class, $autoload), $traits);
        } while ($class = get_parent_class($class));

        // Get traits of all parent traits
        $traitsToSearch = $traits;
        while (! empty($traitsToSearch)) {
            $newTraits = class_uses(array_pop($traitsToSearch), $autoload);
            $traits = array_merge($newTraits, $traits);
            $traitsToSearch = array_merge($newTraits, $traitsToSearch);
        };

        foreach ($traits as $trait => $same) {
            $traits = array_merge(class_uses($trait, $autoload), $traits);
        }

        return array_unique($traits);
    }

    public function isPropertyLoaded($object, $propertyName)
    {
        $objectHash = spl_object_hash($object);
        $this->fixObjectInIdentityMap($object, $objectHash);

        return isset($this->identityMap[$objectHash][$propertyName]);
    }

    public function markPropertyLoaded($object, $propertyName)
    {
        $objectHash = spl_object_hash($object);

        $this->registerObjectToIdentityMap($object, $objectHash);
        $this->identityMap[$objectHash][$propertyName] = true;

        //Properties which has the same provider as $propertyName are marked loaded.
        $metadata = $this->getMetadata(get_class($object));
        foreach ($metadata->getFieldsWithSameDataSource($propertyName) as $otherLoadedPropertyName) {
            $this->identityMap[$objectHash][$otherLoadedPropertyName] = true;
        }
    }

    private function fixObjectInIdentityMap($object, $objectHash)
    {
        //Checks if hash is orphan.
        //This is possible because when a object dead, it's hash is reused on another object. 

        $this->checkIfIsLoadable($object);   
        if (false === $object->__isRegistered) {
            unset($this->identityMap[$objectHash]); 
        }
    }

    private function registerObjectToIdentityMap($object, $objectHash)
    {
        if (! isset($this->identityMap[$objectHash])) {
            $this->identityMap[$objectHash] = [];
            $this->checkIfIsLoadable($object);
            $object->__isRegistered = true;
        }
    }

    public function addVariables($object, array $variables)
    {
        $objectHash = spl_object_hash($object);

        $this->registerObjectToIdentityMap($object, $objectHash);
        $this->identityMap[$objectHash]->variables = $variables;
    }
   
    /**
     * Retrieve other properties loaded when $propertyName is loaded.
     *
     * @param array $propertyName A property name.
     *
     * @return array
     */
    public function getPropertiesLoadedTogether($object, $propertyName)
    {
        $metadata = $this->getMetadata(get_class($object));
        return $metadata->getFieldsWithSameDataSource($propertyName);
    }

    public function manage($object)
    {
        $this->resultManager->manage($object);
    }

    public function unmanage($object)
    {
        $this->resultManager->unmanage($object);
    }

    /**
     * Factory method to get an hydrator instance or create an hydrator if no instance available.
     *
     * @param $objectClass Object class to hydrate
     *
     * @return AbstractHydrator
     */
    public function getHydratorFor($objectClass)
    {
        if (! isset($this->hydratorInstances[$objectClass])) {

            $this->hydratorInstances[$objectClass] = $this->createHydratorFor($objectClass);
        }

        return $this->hydratorInstances[$objectClass];
    }

    /**
     * Factory method to create an hydrator.
     *
     * @param $objectClass Object class to hydrate
     *
     * @return AbstractHydrator
     */
    public function createHydratorFor($objectClass)
    {
        $metadata = $this->getMetadata($objectClass);
        $propertyAccessStrategy = $metadata->isPropertyAccessStrategyEnabled();

        $hydrator = new Hydrator\Hydrator($this, $propertyAccessStrategy);
        if ($this->classResolver) {
            $hydrator->setClassResolver($this->classResolver);
        }

        $fieldsWithHydrationStrategy = $metadata->computeFieldsWithHydrationStrategy();

        $mappedFieldNames = $metadata->getMappedFieldNames();

        foreach ($mappedFieldNames as $mappedFieldName) {

            $strategy = null;

            if ($metadata->isMappedFieldWithStrategy($mappedFieldName)) {

                $fieldStrategy = $fieldsWithHydrationStrategy[$mappedFieldName];
                $strategy = new ClosureHydrationStrategy(
                    $fieldStrategy[$metadata::INDEX_EXTRACTION_STRATEGY],
                    $fieldStrategy[$metadata::INDEX_HYDRATION_STRATEGY]
                );
            }

            if ($metadata->isMappedDateField($mappedFieldName)) {

                $readDateConverter = $metadata->getReadDateFormatByMappedField($mappedFieldName, null);
                $writeDateConverter = $metadata->getWriteDateFormatByMappedField($mappedFieldName, null);

                if (! is_null($readDateConverter) && ! is_null($writeDateConverter)) {
                    $strategy = new DateHydrationStrategy($readDateConverter, $writeDateConverter, $strategy);
                } else {
                    throw new ObjectMappingException(
                        sprintf(
                            'The date field "%s" should provide "readDateConverter" and "writeDateConverter" metadata.',
                            $mappedFieldName
                        )
                    );
                }
            }

            if (! is_null($strategy)) {
                $hydrator->addStrategy($mappedFieldName, $strategy);
            }
        }

        //------------------------------------------------------------------------------------------
        if ($metadata->eventsExist()) {
            $hydrator = new Hydrator\EventHydrator($hydrator, $this);
        }

        return $hydrator;
    }

    public function findFromSource(Source $sourceMetadata)
    {
        $class = $sourceMetadata->getMethod()->getClass();
        $source = $this->classResolver ? $this->classResolver->resolve($class) : new $class;
        $cacheKey = $sourceMetadata->getId() . $class . $sourceMetadata->getMethod()->getFunction();

        return $this->methodInvoker->invoke(
            $source, 
            $sourceMetadata->getMethod()->getFunction(), 
            $sourceMetadata->getMethod()->getArgs(), 
            $this->cacheProfile ? $this->cacheProfile->setKey($cacheKey)->derive() : null
        );
    }

    public function getRepository($objectClass)
    {
        $metadata = $this->getMetadata($objectClass);
        $repositoryClass = $metadata->getRepositoryClass();

        if (! isset($repositoryClass)) {
            throw new \LogicException(sprintf('No repository class found in [%s] metadata.', $objectClass));
        }

        return $this->classResolver ? $this->classResolver->resolve($repositoryClass) : new $repositoryClass;
    }

    /**
    * Return the class metadata.
    *
    * @param string $className FQCN without a leading back slash as does get_class()
    *
    * @return \Kassko\DataMapper\ClassMetadata\ClassMetadata
    */
    public function getMetadata($objectClass)
    {
        if (! $objectClass instanceof ObjectKey) {
            $objectClass = new ObjectKey($objectClass);
        }

        $key = $objectClass->getKey();

        return $this->classMetadataFactory->loadMetadata(
            $objectClass,
            LoadingCriteria::createFromConfiguration($this->configuration, $objectClass),
            $this->configuration
        );
    }

    public function setClassMetadataFactory(ClassMetadataFactoryInterface $classMetadataFactory)
    {
        $this->classMetadataFactory = $classMetadataFactory;
        return $this;
    }

    public function getConfiguration()
    {
        return $this->configuration;
    }

    public function setConfiguration(Configuration $configuration)
    {
        $this->configuration = $configuration;

        return $this;
    }

    public function getResultManager()
    {
        return $this->resultManager;
    }

    public function setObjectListenerResolver(ObjectListenerResolverInterface $objectListenerResolver)
    {
        $this->objectListenerResolver = $objectListenerResolver;

        return $this;
    }

    /**
     * Sets a class resolver.
     *
     * @param ClassResolverInterface $classResolver A class resolver 
     *
     * @return self
     */
    public function setClassResolver(ClassResolverInterface $classResolver)
    {
        $this->classResolver = $classResolver;

        return $this;
    }

    /**
     * Unsets the class resolver.
     *
     * @return self
     */
    public function unsetClassResolver()
    {
        $this->classResolver = null;

        return $this;
    }

    public function executeCommand(Callable $command)
    {
        $command();

        return $this;
    }

    public function setLogger(LoggerInterface $logger = null)
    {
        $this->logger = $logger;

        return $this;
    }

    public function hasListeners()
    {
        return isset($this->objectListenerResolver);
    }

    public function registerEvents($objectClassName)
    {
        if (! isset($this->objectListenerResolver)) {
            return;
        }

        $metadata = $this->getMetadata($objectClassName);
        $listenerClasses = $metadata->getObjectListenerClasses();

        if (isset($listenerClasses)) {
            foreach ($listenerClasses as $listenerClass) {
                $this->objectListenerResolver->registerEvents($listenerClass, self::$eventToRegisterData);
            }
        }
    }

    public function dispatchEvent($objectClassName, $eventName, Callable $eventFactoryMethod)
    {
        if (! $this->hasListeners()) {
            return;
        }

        $metadata = $this->getMetadata($objectClassName);
        $listenerClasses = $metadata->getObjectListenerClasses();

        if (isset($listenerClasses)) {
            foreach ($listenerClasses as $listenerClass) {
                $this->objectListenerResolver->dispatchEvent($listenerClass, $eventName, $eventFactoryMethod());
            }
        }
    }

    /**
     * Gets the value of expressionLanguageEvaluator.
     *
     * @return ExpressionLanguageEvaluator
     */
    public function getExpressionLanguageEvaluator()
    {
        return $this->expressionLanguageEvaluator;
    }

    /**
     * Sets the value of expressionLanguageEvaluator.
     *
     * @param ExpressionLanguage $expressionLanguageEvaluator the expression language
     *
     * @return self
     */
    public function setExpressionLanguageEvaluator(ExpressionLanguageEvaluator $expressionLanguageEvaluator)
    {
        $this->expressionLanguageEvaluator = $expressionLanguageEvaluator;

        return $this;
    }

    /**
     * Gets the value of expressionContext.
     *
     * @return ExpressionContext
     */
    public function getExpressionContext()
    {
        return $this->expressionContext;
    }

    /**
     * Sets the value of expressionContext.
     *
     * @param ExpressionContext $expressionContext the expression context
     *
     * @return self
     */
    public function setExpressionContext(ExpressionContext $expressionContext)
    {
        $this->expressionContext = $expressionContext;

        return $this;
    }

    /**
     * Gets the value of methodInvoker.
     *
     * @return MethodInvoker
     */
    public function getMethodInvoker()
    {
        return $this->methodInvoker;
    }

    /**
     * Sets the value of methodInvoker.
     *
     * @param MethodInvoker $methodInvoker the method invoker
     *
     * @return self
     */
    public function setMethodInvoker(MethodInvoker $methodInvoker)
    {
        $this->methodInvoker = $methodInvoker;

        return $this;
    }

    /**
     * Sets the value of cacheProfile.
     *
     * @param CacheProfile $cacheProfile the cache manager
     *
     * @return self
     */
    public function setCacheProfile(CacheProfile $cacheProfile = null)
    {
        $this->cacheProfile = $cacheProfile;

        return $this;
    }

    /**
     * Unsets the cacheProfile.
     *
     * @return self
     */
    public function unsetCacheProfile()
    {
        $this->cacheProfile = null;

        return $this;
    }
}
