<?php

namespace Kassko\DataMapper;

use Doctrine\Common\Annotations\AnnotationReader;
use Kassko\DataMapper\ClassMetadataLoader\AnnotationLoader;
use Kassko\DataMapper\ClassMetadataLoader\DelegatingLoader;
use Kassko\DataMapper\ClassMetadataLoader\LoaderResolver;
use Kassko\DataMapper\ClassMetadataLoader\PhpFileLoader;
use Kassko\DataMapper\ClassMetadataLoader\InnerPhpLoader;
use Kassko\DataMapper\ClassMetadataLoader\YamlFileLoader;
use Kassko\DataMapper\ClassMetadataLoader\InnerYamlLoader;
use Kassko\DataMapper\ClassMetadata\ClassMetadataFactory;
use Kassko\DataMapper\Configuration\CacheConfiguration;
use Kassko\DataMapper\Configuration\ClassMetadataFactoryConfigurator;
use Kassko\DataMapper\Configuration\ConfigurationChain;
use Kassko\DataMapper\LazyLoader\LazyLoaderFactory;
use Kassko\DataMapper\Registry\Registry;
use Symfony\Component\Config\Definition\Processor;

class DataMapperFactory
{
    /**
     *  [
     *      'mapping' => [
     *          'default_resource_type' =>  //annotations # Default is "annotations" or other type (1).
     *          'default_resource_dir' => //Optional.
     *          'default_provider_method' => //Optional.
     *          'groups' =>
     *          [//optional section
     *              'some_group' =>
     *              [
     *                  'resource_type' => //annotations # Default is "annotations" or other type (1).
     *                  'resource_dir' => //The resource dir of the given bundle.
     *                  'provider_method' => null //Required. Default value is null.
     *              ],
     *          ],
     *          'objects' =>
     *          [// Optional section.
     *              [
     *                  'class' => //Required (full qualified object class name).
     *                  'group' => //Optional.
     *                  'resource_type' => //Optional.
     *                  'resource_path' => //Optional. The resource directory with the resource name. If not defined, data-mapper fallback to resource_name and prepend to it resource_dir (or default_resource_dir). So if resource_path is not defined, case resource_name and resource_dir (or default_resource_dir) must be defined.
     *                  'resource_name' => //Optional. Only the resource name (so without the directory).
     *                  'provider_method' => //Optional. Override default_provider_method.
     *              ],
     *          ],
     *      ],
     *     'logger' =>//Optional. A logger service name. Il will be used for logging in data-mapper component.
     *     'cache' =>
     *      [
     *         'metadata_cache' =>
     *          [ //Optional section
     *             'adapter_class' => //Default is "Kassko\Bundle\DataMapperBundle\Adapter\Cache\DoctrineCacheAdapter"
     *             'life_time' => //Default is 0
     *             'is_shared' => //Default is false
     *         ],
     *         'result_cache' => //Optional section and the same structure as metadata_cache
     *     ]
     *  ],
     *
     * (1) availables types are annotations, yaml, php, php_file, yaml_file.
     * And maybe others if you add some custom mapping loaders.
     */
    private $pSettings = [];
    private static $cacheInterface = 'Kassko\\Cache\\CacheInterface';

    /**
     * Instantiate a DataMapper from settings
     *
     * @return self
     */
    public function instance()
    {
        $settings = $this->getValidatedSettings();

        $defaultResourceDir = isset($settings['mapping']['default_resource_dir']) ? $settings['mapping']['default_resource_dir'] : null;
        $defaultClassMetadataProviderMethod = isset($settings['mapping']['default_provider_method']) ? $settings['mapping']['default_provider_method'] : null;

        $classResolver = isset($settings['class_resolver']) ? isset($settings['class_resolver']) : null;
        $objectListenerResolver = isset($settings['object_listener_resolver']) ? isset($settings['object_listener_resolver']) : null;
        $logger = isset($settings['logger']) ? isset($settings['logger']) : null;

        $loaders = [
            //TODO: instantiate only the loaders that are required in the settings.
            new AnnotationLoader(new AnnotationReader),
            new YamlFileLoader(),
            new InnerYamlLoader(),
            new PhpFileLoader(),
            new InnerPhpLoader(),
        ];

        //Configuration
        $configuration = (new ConfigurationChain)

            ->setClassMetadataCacheConfig(

                (new CacheConfiguration())
                    ->setCache($settings['cache']['metadata']['instance'])
                    ->setLifeTime($settings['cache']['metadata']['life_time'])
                    ->setShared($settings['cache']['metadata']['is_shared'])
            )

            ->setResultCacheConfig(

                (new CacheConfiguration())
                    ->setCache($settings['cache']['result']['instance'])
                    ->setLifeTime($settings['cache']['result']['life_time'])
                    ->setShared($settings['cache']['result']['is_shared'])
            )

            ->setDefaultClassMetadataResourceType($settings['mapping']['default_resource_type'])
            ->setDefaultClassMetadataResourceDir($defaultResourceDir)
            ->setDefaultClassMetadataProviderMethod($defaultClassMetadataProviderMethod)
        ;

        //Mapping
        foreach ($settings['mapping']['objects'] as $objectSettings) {

            if (isset($objectSettings['group'])) {

                $group = $objectSettings['group'];

                if (! isset($settings['mapping']['groups'][$group]) ) {

                    throw new LogicException(
                        sprintf(
                            'The group "%s" is used in path mapping.objects.[%s] but not defined in mapping.groups',
                            $group,
                            $objectSettings
                        )
                    );
                }

                $groupSettings = $settings['mapping']['groups'][$group];

                $parentClassMetadataResourceType = isset($groupSettings['resource_type']) ? $groupSettings['resource_type'] : null;
                $parentClassMetadataProviderMethod = isset($groupSettings['provider_method']) ? $groupSettings['provider_method'] : null;

                if (isset($groupSettings['resource_path'])) {
                    $parentClassMetadataResourcePath = trim($groupSettings['resource_path']);
                }

                if (isset($groupSettings['resource_dir'])) {
                    $classMetadataResourceDir = $groupSettings['resource_dir'];
                }
            }

            if (isset($objectSettings['resource_type'])) {
                $classMetadataResourceType = trim($objectSettings['resource_type']);
            }

            if (isset($objectSettings['resource_path'])) {
                $classMetadataResource = trim($objectSettings['resource_path']);
            } elseif (isset($objectSettings['resource_name'])) {
                $classMetadataResource = $classMetadataResourceDir.'/'.$objectSettings['resource_name'];
            }

            $mappingObjectClass = trim($objectSettings['class']);

            if (isset($classMetadataResourceType)) {
                $configuration->addClassMetadataResourceType($mappingObjectClass, $classMetadataResourceType);
            } elseif (isset($parentClassMetadataResourceType)) {
                $configuration->addClassMetadataResourceType($mappingObjectClass, $parentClassMetadataResourceType);
            }

            if (isset($classMetadataResource)) {
                $configuration->addClassMetadataResource($mappingObjectClass, $classMetadataResource);
            }

            if (isset($classMetadataProviderMethod)) {
                $configuration->addClassMetadataProviderMethod($mappingObjectClass, $classMetadataResource);
            } elseif (isset($parentClassMetadataProviderMethod)) {
                $configuration->addClassMetadataProviderMethod($mappingObjectClass, $parentClassMetadataProviderMethod);
            }

            if (isset($classMetadataDir)) {
                $configuration->addClassMetadataDir($mappingObjectClass, $classMetadataDir);
            } elseif (isset($parentClassMetadataDir)) {
                $configuration->addClassMetadataDir($mappingObjectClass, $parentClassMetadataDir);
            }
        }

        //ClassMetadataFactory
        $delegatingLoader = new DelegatingLoader(
            new LoaderResolver($loaders)
        );
        $cmFactory = (new ClassMetadataFactory)->setClassMetadataLoader($delegatingLoader);
        $cmConfigurator = new ClassMetadataFactoryConfigurator($configuration);
        $cmConfigurator->configure($cmFactory);

        //ObjectManager
        $objectManager = ObjectManager::getInstance()
            ->setConfiguration($configuration)
            ->setClassMetadataFactory($cmFactory)
        ;

        if (isset($classResolver)) {
            $objectManager->setClassResolver($classResolver);
        }

        if (isset($objectListenerResolver)) {
            $objectManager->setObjectListenerResolver($objectListenerResolver);
        }

        if (isset($logger)) {
            $objectManager->setClassResolver($logger);
            Registry::getInstance()[Registry::KEY_LOGGER] = $logger;
        }

        $lazyLoaderFactory = new LazyLoaderFactory($objectManager);
        Registry::getInstance()[Registry::KEY_LAZY_LOADER_FACTORY] = $lazyLoaderFactory;

        return new DataMapper($objectManager);
    }

    /**
     * Add settings
     *
     * @param array $settings The settings
     *
     * @return self
     */
    public function settings(array $settings)
    {
        $this->pSettings[] = Utils::getUnpackedSettings($settings);

        return $this;
    }

    /**
     * Clear settings
     *
     * @return self
     */
    public function clearSettings()
    {
        $this->pSettings = [];
        return $this;
    }

    /**
     * Validates settings and reduces them if multiple settings given.
     */
    private function getValidatedSettings()
    {
        $processor = new Processor();
        $settingsValidator = new SettingsValidator();
        $validatedSettings = $processor->processConfiguration(
            $settingsValidator,
            $this->pSettings
        );

        return $validatedSettings;
    }
}
