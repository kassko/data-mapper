`0.12.7.0 - 2018/10/14 - New version`:
* `New`: Add shortcuts for the feature about property loaded. No need to use the service ObjectManager and go through `ObjectManager::markPropertyLoaded()` or `ObjectManager::isPropertyLoaded()` anymore. From now there are shorcuts `LoadableTrait::markPropertyLoaded()` and `LoadableTrait::isPropertyLoaded()` and then methods are accessibles from objects which use the trait `LoadableTrait`.

`0.12.6.0 - 2015/11/24 - New version`:
* `New`: [Support][Symfony] Add support for version 3.0 of Symfony components.

`0.12.5.6 - 2015/11/12 - Fix version`:
* `New`: [Documentation][DataSource] Fix some typo.

`0.12.5.5 - 2015/11/12 - Fix version`:
* `New`: [Documentation][DataSource] Improve Documentation about data sources.

`0.12.5.4 - 2015/11/12 - Fix version`:
* `New`: [Dependency Injection Usage][DataMapperBuilder] Hide the concept class resolver.

`0.12.5.3 - 2015/11/10 - Fix version`:
* `New`: [General][MIT] Add MIT.

`0.12.5.2 - 2015/10/20 - Fix version`:
* `Fix`: [DataSource][Source cache key] Fix source cache key by including source method arguments in the key.

`0.12.5.1 - 2015/10/19 - Fix version`:
* `Fix`: [DataSource][Arguments resolution] DataSource arguments were not resolved properly when DataSource config was shared between several instances (DataSource sharing occurs when we work with collection). The resolution was processed only once, now it's processed for each instance of the collection.

`0.12.5.0 - 2015/10/06 - New version`:
* `New`: Allows to perform relations between severals sources.
* `New`: New config item `RefDefaultSource` which allows to bind a default source to all the fields of an object. That's usefull not to duplicate on all fields the config item RefSource. It's possible to exclude some fields to be bound with another new config item `ExcludeDefaultSource`.
* `New`: Source loading: new trait `LoadableTrait` with a method `load` which load all properties (except properties marked to be lazy loaded). That's usefull in combination with the new config items `RefDefaultSource` and `ExcludeDefaultSource`. It's also fix an inconsistency: only the mode `lazyloading enabled` worked. We were not able to load immediately properties.
* `New`: Hydrator configuration: new key `depends` in source config item that allows to specify some sources as dependencies. These sources will always be triggered before the dependant source.
* `New`: Allows variables in configuration. Variables are send from an object to another nested object and are availables in the configuration via an expression language.
* `New`: New key `defaultValue` (a smart default value) in config item `Field` usefull to initialize properties from configuration and expression language.
* `New`: New config item `Config`. It replaces `ValueObject config` which is deprecated now.
* `New`: Merge Provider to DataSource concept. So just use DataSource instead of Provider. Provider concept will be removed in the next significant release.
* `New`: key config `class_resolver`: make invisible the use of `class-resolver`. You can just send a closure which is internally convert to a `Kassko\ClassResolver\CallableClassResolver`.
* `New`: key config `object_listener_resolver`: like for `class_resolver`, now you can just send a closure.
* `New`: add a new method `unsetClassResolver()` to unset the class resolver from ObjectManager.
* `New`: add a new method `unsetClassResolver()` to unset the class resolver from Hydrator\Hydrator.
* `New`: add a new method `unsetCacheProfile()` to unset the cache profile from ObjectManager. By default, data sources results are cached in an `array` cache to be reused during all the hydration process. Results are cached because they can be used by others data sources as parameter/input). Unsetting the cache profile allows to disable data sources result caching. Usefull in unit tests to really call data sources and to expect a number of calls.
* `Fix`: Fix bug on class-resolver. We were not able to set a class-resolver. A boolean value "true" was set instead.
* `Fix`: Fix bug on extraction. Members with no getters were never extracted.
* `Fix`: Fix bug on extraction. Properties which are objects were not extracted.
* `Fix`: Fix identification of fields with same source when sources have same class/method but differents method arguments.  Now, use the Id to process the identification instead of the pair class/method
* `Fix`: Fix bug when working whith several object managers. The lazy loader didn't retrieve the good one.
* `Fix`: Hydrator configuration: Fix a lot of things in each format configuration.
* `Fix`: Hydrator configuration: Yaml ans Php format: Make useless to specify keys with a value empty or null. Just don't specify them.
* `Fix`: Hydrator configuration: Fix key `processors` in annotation configuration. Processors specified was ignored.
* `Fix`: Hydrator configuration: Fix key `interceptors` in yaml configuration format.
* `Fix`: Hydrator configuration: Fix key `interceptors` in php configuration format.
* `Enhanc`: Add some hydrator tests
* `Enhanc`: Add a default cache for data sources. An array / request cache.

`0.12.4.4 - 2015/08/17 - Fix version`:
* `Fix`: Fix `loadProperty()` access in inheritance context (make this method protected).

`0.12.4.3 - 2015/06/15 - Fix version`:
* `Fix`: Fix sources loading (only the first source was loaded sometimes) and simplify the code related to the sources loaded tracking.

`0.12.4.2 - 2015/04/17 - Fix version`:
* `Fix`: Hydrator configuration: Section Field / Key type: fix inference of type, if the key `type` is not specified, an implicit conversion to the supposed good type is performed. Conversion to string was performed.

`0.12.4.1 - 2015/04/16 - Fix version`:
* `Fix`: Fix lazyloading in inheritance context. Between two properties in the same hierarchy, only one was loaded and an error occured when we tried to load the second.

`0.12.4.0 - 2015/04/12 - New version`:
* `New`: Hydrator configuration: add expression language for methods arguments.
* `New`: Hydrator configuration: add processors (some methods) to do some stuff before or after the invocation of a source.
* `New`: Hydrator configuration: enhance type - allows to enforce type of fields.
* `Fix`: Hydrator configuration: allows to invoke __call method.
* `Fix`: Data Mapper configuration: fix forgotten keys `class_resolver` and `object_listener_resolver`.

`0.12.3.0 - 2015/03/30 - New version`:
* `New`: Allows to hydrate a property with value null.

`0.12.2.2 - 2015/03/29 - Fix version`:
* `Fix`: Fix conflict between data sources with same id in different object.

`0.12.2.1 - 2015/03/26 - Fix version`:
* `Fix`: Fix Doctrine entity incompatibility.

`0.12.2.0 - 2015/03/25 - New version`:
* `New`: Add data source key "id" which replaces the key "ref".

`0.12.1.1 - 2015/03/25 - Fix version`:
* `Fix`: Fix bug on hydration of more than 2 nested objects from a DataSource.

`0.12.1.0 - 2015/03/25 - New version`:
* `New`: Add DataSourcesStore and ProvidersStore to centralize all sources.
* `New`: Allows to link a property to source from the store to reduce the mapping code.
* `New`: Allows to chaine sources as fallback sources if some sources do not respond properly.
* `New`: Use stable version of class-resolver component (no an alpha version)

`0.12.0.0-alpha - 2015/03/24 - Break version`:
* `Break`: To decide if a field should be managed, in your mapping configuration use the keys Include/Exclude/Object.fieldExclusionPolicy. The key Field don't do the job anymore.
* `Break`: To send the curent object as parameter of a data source method use ##this instead of @this. To send the value of a property, use #some_property instead of some_property.
* `Break`: Relations ToOneProvider and ToManyProvider have been removed. They will be implemented in DataSource and Provider in a next version.
* `Break`: DataMapperFactory is moved to DataMapperBuilder.

* `New`:
You can send some arbitrary values as parameters of a data source method (2, 'foo'). That's why conventions have changed.
New key Fields.class which allows to hydrate properties which are objects.
And as a consequence, Data sources can now return structure like tree from which nested objects can be hydrated.

`0.11.0.0-alpha - 2015/03/11 - New version`:
* ` Break `: The object is no longer send as parameter of the data source method. If you still need this object, set the new key "args" of the data source key to "args={"@this"}".

* ` New `: New key in mapping configuration "data source / object class" to allow the automatic hydration of nested objects. You can send parameters to your data source method like the curent object "args={"@this"}" or some key of the raw data like "args={"some_key_value"}" and several arguments like that "args={"@this", "some_key_value"}".
` Improvement ` You no longer need to create getters and setters in your objects. If no getters and setters are found, the mapper access directly the properties (even if there are private or protected).

* ` Fix `: Fix bad interpretation of php mapping format configuration

`0.10.0.0-alpha - 2015/03/09 - Break version:`
* ` Break `: In domain object mapping configuration, move provider concept to a data source concept and the provider key name is moved to something as "data source" in all configuration format.

* ` New `: A data source concept which improves the previous provider concept (removed). It does better the work to allow to hydrate some properties from a specific source.

* ` Fix `: Fix bug in domain object using an unavailable extension. For example, if the lazyloading extension is unavailable, the code for it (the loadProperty() method) is disable and the program no longer crashes.

`0.9.0.3-alpha - 2015/01/04 - Break version:`
* ` Fix `: Fix a bug which occurs sometimes when using the Hydrator::loadPropertyMethod()

`0.9.0.2-alpha - 2015/01/04 - Break version:`
* ` Fix `: Fix travis information

`0.9.0.1-alpha - 2015/01/04 - Break version:`
* ` Fix `: Fix some bad component version requirements

`0.9.0.0-alpha - 2015/01/04 - Break version:`
* ` New `: Add a high level configuration to facilitate to create a DataMapper instance

* ` Break `: Rename some keys in mapping configuration

`0.8.0.0-alpha - 2014/12/22 - Break version:`
* ` New `: Add a method to get an hydrator from the DataMapper class

* ` Break `: Modify the DataMapper class API, rename the methods with shorter names

* ` Break `: Modify the ResultBuilder class API, rename the methods with shorter names

`0.7.1.0-alpha - 2014/12/16 - NEW version:`
* ` New `: Add a method to get the configuration from the DataMapper class

`0.7.0.0-alpha - 2014/12/16 - BREAK version:`
* ` New `: Add a class Kassko\DataMapper\DataMapper which contains the interface of Kassko\DataMapper\Result\ResultBuilderFactory and Kassko\DataMapper\Query\QueryFactory

* ` Break `: The object Kassko\DataMapper\Result\ResultBuilderFactory is removed, use Kassko\DataMapper\DataMapper instead with the same API. Exception the create() method is replaced by createResultBuilder().

* ` Break `: The class Kassko\DataMapper\Query\QueryFactory is removed, use Kassko\DataMapper\DataMapper instead with the same API.
