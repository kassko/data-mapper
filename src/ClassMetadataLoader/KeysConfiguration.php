<?php

namespace Kassko\DataMapper\ClassMetadataLoader;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder; 
use Symfony\Component\Config\Definition\ConfigurationInterface;

class KeysConfiguration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();
        $rootNode = $builder->root('root');

        $rootNode//->addDefaultsIfNotSet()     
            ->children()
                ->arrayNode('object')//->addDefaultsIfNotSet()
                    ->append($this->sourceStoreNode('dataSourcesStore'))
                    ->append($this->sourceStoreNode('providersStore'))
                    ->children()
                        ->enumNode('fieldExclusionPolicy')/** @deprecated @see root.fieldExclusionPolicy */
                            ->values(['include_all', 'exclude_all'])
                            //->defaultValue('include_all')
                        ->end()
                        ->scalarNode('readDateConverter')->defaultNull()->end()
                        ->scalarNode('writeDateConverter')->defaultNull()->end()
                        ->scalarNode('classMappingExtensionClass')->defaultNull()->end()
                        ->scalarNode('fieldMappingExtensionClass')->defaultNull()->end()
                        ->booleanNode('propertyAccessStrategy')->defaultFalse()->end()
                        ->arrayNode('customHydrator')//->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('class')->defaultNull()->end()
                                ->scalarNode('hydrateMethod')->defaultNull()->end()
                                ->scalarNode('extractMethod')->defaultNull()->end()
                            ->end()
                        ->end()
                        ->scalarNode('providerClass')->defaultNull()->end()
                        ->booleanNode('readOnly')->defaultFalse()->end()
                    ->end()
                ->end()
                ->arrayNode('fields')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('name')->defaultNull()->end()
                            ->scalarNode('type')->defaultNull()->end()
                            ->scalarNode('class')->defaultNull()->end()
                            ->scalarNode('defaultValue')->defaultNull()->end()
                            ->scalarNode('readConverter')->defaultNull()->end()
                            ->scalarNode('writeConverter')->defaultNull()->end()
                            ->scalarNode('readDateConverter')->defaultNull()->end()
                            ->scalarNode('writeDateConverter')->defaultNull()->end()
                            ->scalarNode('fieldMappingExtensionClass')->defaultNull()->end()
                            ->scalarNode('refSource')->defaultNull()->end()
                            ->append($this->propertyWrapperNode('getter'))
                            ->append($this->propertyWrapperNode('setter'))
                            ->append($this->sourceNode('dataSource'))
                            ->append($this->sourceNode('provider'))
                            ->append($this->configNode('config'))
                            ->append($this->smartScalarNode('variables'))
                        ->end()
                        ->validate()
                        ->always(function ($v) {
                            if (empty($v['config']) && ! empty($v['valueObjects'])) {
                                $v['config'] = $v['valueObjects'];
                                unset($v['include']);
                            }
                            return $v;
                        })
                        ->end()
                    ->end()
                ->end()
                ->enumNode('fieldExclusionPolicy')
                    ->values(['include_all', 'exclude_all'])
                    //->defaultValue('include_all')
                ->end()
                ->append($this->smartScalarNode('fieldsToInclude'))
                ->append($this->smartScalarNode('fieldsToExclude'))
                ->append($this->smartScalarNode('include')) /** @deprecated @see fieldsToInclude **/
                ->append($this->smartScalarNode('exclude')) /** @deprecated @see fieldsToExclude **/
                ->append($this->smartScalarNode('fieldsNotToBindToDefaultSource')) 
                ->scalarNode('refDefaultSource')->defaultNull()->end()
                ->arrayNode('listeners')//->addDefaultsIfNotSet()
                    ->append($this->methodsNode('preHydrate'))
                    ->append($this->methodsNode('postHydrate'))
                    ->append($this->methodsNode('preExtract'))
                    ->append($this->methodsNode('postExtract'))
                ->end()
                ->arrayNode('interceptors')
                    ->children()
                        ->append($this->interceptorNode('preHydrate'))
                        ->append($this->interceptorNode('postHydrate'))
                        ->append($this->interceptorNode('preExtract'))
                        ->append($this->interceptorNode('postExtract'))
                    ->end()
                ->end()
                ->append($this->smartScalarNode('objectListeners'))
                ->append($this->valueObjectsNode('valueObjects'))
                ->scalarNode('id')->defaultNull()->end()
                ->arrayNode('idComposite')->prototype('scalar')->end()->end()
                ->append($this->smartScalarNode('transient'))
                ->scalarNode('version')->defaultNull()->end()
            ->end()
            ->validate()
            ->always(function ($v) {
                if (! isset($v['fieldExclusionPolicy'])) {
                    if (isset($v['object']['fieldExclusionPolicy'])) {
                        $v['fieldExclusionPolicy'] =  $v['object']['fieldExclusionPolicy'];
                        unset($v['object']['fieldExclusionPolicy']); 
                    } else {
                        $v['fieldExclusionPolicy'] = 'include_all';
                    }
                }

                if (empty($v['fieldsToExclude']) && ! empty($v['exclude'])) {
                    $v['fieldsToExclude'] = $v['exclude'];
                    unset($v['exclude']);
                }

                if (empty($v['fieldsToInclude']) && ! empty($v['include'])) {
                    $v['fieldsToInclude'] = $v['include'];
                    unset($v['include']);
                }

                if (isset($v['valueObjects']) && ! empty($v['valueObjects']) && isset($v['fields']) && ! empty($v['fields'])) {
                    $fields = &$v['fields'];
                    foreach ($fields as &$field) {
                        $field['config'] = $v['valueObjects'][$field['name']];
                    }
                }

                return $v;
            })
            ->end()
        ;

        //@todo     Implement in all loaders
        //variables

        return $builder;
    }

    private function propertyWrapperNode($nodeName)
    {
        $builder = new TreeBuilder();
        $node = $builder->root($nodeName);

        $node
            //->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('prefix')->defaultNull()->end()
                ->scalarNode('name')->defaultNull()->end()
            ->end()
        ;
        
        return $node;
    }

    private function sourceStoreNode($nodeName)
    {
        $builder = new TreeBuilder();
        $node = $builder->root($nodeName);

        $childNode = $node->prototype('array');
        $this->configureSourceNode($childNode);
        
        return $node;
    }

    private function sourceNode($nodeName)
    {
        $builder = new TreeBuilder();
        $node = $builder->root($nodeName);

        $this->configureSourceNode($node);
        
        return $node;
    }

    private function configureSourceNode(NodeDefinition $node)
    {
        $this->configureMethodNode($node);
        
        $node
            //->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('id')->defaultNull()->end()
                ->booleanNode('lazyLoading')->defaultFalse()->end()
                ->booleanNode('supplySeveralFields')->defaultFalse()->end()
                ->arrayNode('depends')->prototype('scalar')->defaultValue([])->end()->end()
                ->enumNode('onFail')
                    ->defaultValue('checkReturnValue')
                    ->values(['checkReturnValue', 'checkException'])
                ->end()
                ->scalarNode('exceptionClass')->defaultValue('\Exception')->end()
                ->enumNode('badReturnValue')
                    ->defaultValue('null')
                    ->values(['null', 'false', 'emptyString', 'emptyArray'])
                ->end()
                ->scalarNode('fallbackSourceId')->defaultNull()->end()
            ->end()
            ->append($this->methodNode('preprocessor'))
            ->append($this->methodNode('processor'))
            ->append($this->methodsNode('preprocessors'))
            ->append($this->methodsNode('processors'))
        ;
    }

    private function methodsNode($nodesName)
    {
        $builder = new TreeBuilder();
        $node = $builder->root($nodesName);

        $node
            ->beforeNormalization()
                ->always()
                ->then(function ($v) {

                    if (empty($v)) {
                        return $v;
                    }

                    if (isset($v['items'])) {
                        $v = $v['items'];
                    }

                    /**
                     * If only one method, make an array with this only one method
                     * So transforms
                     * ['some_config_key' => ['some_methods_config_key' => ['class' => 'SomeClass', 'method' => someMethod]]] 
                     * to 
                     * ['some_config_key' => [some_methods_config_key => [['class' => 'SomeClass', 'method' => someMethod]]]]
                     */
                    if (! is_array(current($v))) {
                        return [$v];
                    }

                    return $v;
                })
            ->end()
        ;

        $childNode =  $node->prototype('array');
        $this->configureMethodNode($childNode);

        return $node;
    }

    private function methodNode($nodeName)
    {
        $builder = new TreeBuilder();
        $node = $builder->root($nodeName);

        $this->configureMethodNode($node);

        return $node;
    }

    private function configNode($nodeName)
    {
        $builder = new TreeBuilder();
        $node = $builder->root($nodeName);

        $this->configureConfigNode($node);        

        return $node;
    }

    private function valueObjectsNode($nodeName)
    {
        $builder = new TreeBuilder();
        $node = $builder->root($nodeName);

        $childNode =  $node->prototype('array');
        $this->configureConfigNode($childNode);

        return $node;
    }

    private function configureConfigNode(NodeDefinition $node)
    {
        $node
            ->children()
                ->scalarNode('class')->defaultNull()->end()
                ->scalarNode('mappingResourceName')->defaultNull()->end()
                ->scalarNode('mappingResourcePath')->defaultNull()->end()
                ->scalarNode('mappingResourceType')->defaultNull()->end()
            ->end()
        ;
    }

    private function configureMethodNode(NodeDefinition $node)
    {
        $node
            //->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('class')->defaultValue('##this')->end()
                ->scalarNode('method')->defaultNull()->end()
                ->append($this->smartScalarNode('args'))
            ->end()
        ;
    }

    private function interceptorNode($nodeName)
    {
        $builder = new TreeBuilder();
        $node = $builder->root($nodeName);

        $node
            ->children()
                ->scalarNode('class')->defaultNull()->end()
                ->scalarNode('method')->defaultNull()->end()
            ->end()
        ;

        return $node;
    }

    private function smartScalarNode($nodeName)
    {
        $builder = new TreeBuilder();
        $node = $builder->root($nodeName);

        $node
            ->defaultValue([])
            ->beforeNormalization()
                ->always(function ($v) { return (array)$v; })
            ->end()
            ->prototype('scalar')->end()
        ;

        return $node;
    }
}
