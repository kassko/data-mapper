`0.12.2.2 - 2015/03/29 - Fix version`:
* `Fix`: Fix conflict between data sources with same id in different object.

`0.12.2.1 - 2015/03/26 - Fix version`:
* `Fix`: Fix Doctrine entity incompatibility.

`0.12.2.0 - 2015/03/25 - Improvements version`:
* `Improvements`: Add data source key "id" which replaces the key "ref". "ref" is kept but marked deprecated. This key should be removed in the next significant release.

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