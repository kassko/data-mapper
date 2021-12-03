<?php

namespace Kassko\DataMapper;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class SettingsValidator implements ConfigurationInterface
{
    private $cacheInterface = 'Kassko\\DataMapper\\Cache\\ArrayCache';

    public function getConfigTreeBuilder()
    {
        list($rootNode, $builder) = $this->getRootNode('kassko_data_mapper');

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()

                ->arrayNode('mapping')->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('default_resource_type')->defaultValue('annotations')->end()
                        ->scalarNode('default_resource_dir')->end()
                        ->scalarNode('default_provider_method')->end()
                        ->arrayNode('groups')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('resource_type')->end()
                                    ->scalarNode('resource_dir')->end()
                                    ->scalarNode('provider_method')->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('objects')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('class')->isRequired()->end()
                                    ->scalarNode('group')->end()
                                    ->scalarNode('resource_type')->end()
                                    ->scalarNode('resource_path')->end()
                                    ->scalarNode('resource_name')->end()
                                    ->scalarNode('provider_method')->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('expression')->addDefaultsIfNotSet()
                            ->children()
                                //TODO: add markers for service @, field # and semantic ##
                                ->arrayNode('function_providers')
                                    ->prototype('variable')->end()

                                    /*->arrayNode('function_providers')
                                        ->prototype('array')
                                            ->children()
                                            ->end()
                                        ->end()*/
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()

                ->variableNode('logger')->end()

                ->arrayNode('container')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->variableNode('instance')->defaultNull()->end()
                        ->scalarNode('get_method_name')->defaultNull()->end()
                        ->scalarNode('has_method_name')->defaultNull()->end()
                    ->end()
                ->end()

                ->variableNode('class_resolver')/** @deprecated @see key "container" */
                    ->defaultNull()
                    ->beforeNormalization()
                        ->ifTrue(function ($v) {
                            return null !== $v && ! $v instanceof \Kassko\ClassResolver\ClassResolverInterface && ! is_callable($v);
                        })
                        ->then(function ($v) {
                            return new \Kassko\ClassResolver\CallableClassResolver($v);
                        })
                    ->end()
                ->end()

                ->variableNode('object_listener_resolver')
                    ->defaultNull()
                    ->beforeNormalization()
                        ->ifTrue(function ($v) {
                                return null !== $v && ! $v instanceof \Kassko\DataMapper\Listener\ObjectListenerResolverInterface && ! is_callable($v);
                            })
                        ->then(function ($v) {
                            return new \Kassko\DataMapper\Listener\CallableObjectListenerResolver($v);
                        })
                    ->end()
                ->end()

                ->arrayNode('cache')->addDefaultsIfNotSet()
                    ->append($this->addCacheNode('metadata'))
                    ->append($this->addCacheNode('result'))
                ->end()

            ->end()
        ;

        return $builder;
    }

    private function addCacheNode($name)
    {
        list($node, $builder) = $this->getRootNode($name);

        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->variableNode('instance')
                    ->defaultValue(new $this->cacheInterface)
                    ->validate()
                        ->ifTrue(function ($v) {
                            return ! is_subclass_of($v, $this->cacheInterface);
                        })
                        ->thenInvalid(
                            sprintf(
                                'The cache implementation provided should implement "%s".'
                                . ' You could wrap it into an adapter to enforce the implementation of this interface',
                                $this->cacheInterface
                            )
                        )
                    ->end()
                ->end()
                ->scalarNode('life_time')->defaultValue(0)->end()
                ->booleanNode('is_shared')->defaultFalse()->end()
            ->end()
        ;

        return $node;
    }

    private function getRootNode($rootNodeName)
    {
        if (method_exists(TreeBuilder::class, 'getRootNode')) {
            $builder = new TreeBuilder($rootNodeName);
            $rootNode = $builder->getRootNode();
        } else {//Keep compatibility with Symfony <= 4.3
            /**
             * @see https://github.com/symfony/symfony/blob/4.3/src/Symfony/Component/Config/Definition/Builder/TreeBuilder.php#L48
             */
            $builder = new TreeBuilder;
            $rootNode = $builder->root($rootNodeName);
        }

        return [$rootNode, $builder];
    }
}
