Configuration reference
==============

```php
[
    'mapping' =>
    [
        //Default is "annotations" or other type (1).
        'default_resource_type' => 'annotations',

        //Optional key.
        'default_resource_dir' => 'some_dir',

        //Optional key. Has only sense if you use inner_php or inner_yaml format.
        //It's the method whereby you provide the mapping.
        'default_provider_method' => 'some_method_name',

        //Optional section.
        'groups' =>
        [
            'some_group' =>
            [
                //Default is "annotations" or other type (1).
                'resource_type' => annotations,

                //The resource dir of the given bundle.
                'resource_dir' => 'some_dir',

                //Default value is null.
                'provider_method' => null,
            ],
        ],

        //Optional section
        'objects':
        [
            [
                //Required (the full qualified object class name).
                'class' => 'some_fqcn',

                //Optional key. Allows to inherit settings from a group if there are not specified.
                'group' => 'some_group',

                //Optional key.
                'resource_type' => 'yaml_file',

                //Optional key.
                //The resource directory with the resource name.
                //If not defined, data-mapper fallback to resource_name and prepend to it a resource_dir (this object resource_dir or a group resource_dir or the default_resource_dir).
                //So if resource_path is not defined, keys resource_name and a resource_dir should be defined.
                'resource_path' => 'some_path',

                //Optional key. Only the resource name (so without the directory).
                'resource_name' => 'some_ressource.yml',

                //Optional key. Override default_provider_method.
                'provider_method' => 'some_method_name',
            ],
        ],
    ],

    //Optional section.
    'cache' =>
    [
        //Optional section. The cache for mapping metadata.
        'metadata_cache' =>
        [
            //A cache instance which implements Kassko\Cache\CacheInterface. Default is Kassko\Cache\ArrayCache.
            //If you use a third-party cache provider, maybe you need to wrap it into an adapter to enforce compatibility with Kassko\Cache\CacheInterface.
            'instance' => $someCacheInstance,

            //Default value is 0.
            //0 means the data will never been deleted from the cache.
            //Obviously, 'life_time' has no sense with an "ArrayCache" implementation.
            'life_time' => 0,

            //Default value is false. Indicates if the cache is shared or not.
            //If you don't specify it, you're not wrong. It optimises the
            'is_shared' => false,
        ],

        //Optional section. The cache for query results.
        //This section has the same keys as 'metadata_cache' section.
        'result_cache': => [],
    ],

    //Optional key. A logger instance which implements Psr\Logger\LoggerInterface.
    'logger' => $logger,

    //Optional key. Needed to retrieve repositories specified in 'repository_class' mapping attributes and which creation is assigned to a creator (a factory, a container, a callable).
    'class_resolver' => $someClassResolver,

    //Optional key. Needed to retrieve object listener specified in 'object_listener' mapping attributes and which creation is assigned to a creator (a factory, a container, a callable).
    'object_listener_resolver' => $someObjectListenerResolver,
]
```
(1) availables types are annotations, yaml_file, php_file, inner_php, inner_yaml.
And maybe others if you add some custom mapping loaders.