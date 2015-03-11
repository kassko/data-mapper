0.10.1.0-alpha - 2015/03/11 - New version:
** New ** New key in mapping configuration "data source / object class" to allow the automatic hydration of nested objects.
** Improvement ** You no longer need to create getters and setters in your objects. If no getters and setters are found, the mapper access directly the properties (even if there are private or protected).
** Fix ** Fix bad interpretation of php mapping format configuration

0.10.0.0-alpha - 2015/03/09 - Break version:
** Break **: In domain object mapping configuration, move provider concept to a data source concept and the provider key name is moved to something as "data source" in all configuration format.
** New **: A data source concept which improves the previous provider concept (removed). It does better the work to allow to hydrate some properties from a specific source.
** Fix **: Fix bug in domain object using an unavailable extension. For example, if the lazyloading extension is unavailable, the code for it (the loadProperty() method) is disable and the program no longer crashes.

0.9.0.3-alpha - 2015/01/04 - Break version:
** Fix **: Fix a bug which occurs sometimes when using the Hydrator::loadPropertyMethod()

0.9.0.2-alpha - 2015/01/04 - Break version:
** Fix **: Fix travis information

0.9.0.1-alpha - 2015/01/04 - Break version:
** Fix **: Fix some bad component version requirements

0.9.0.0-alpha - 2015/01/04 - Break version:
** New **: Add a high level configuration to facilitate to create a DataMapper instance
** Break **: Rename some keys in mapping configuration

0.8.0.0-alpha - 2014/12/22 - Break version:
** New **: Add a method to get an hydrator from the DataMapper class
** Break **: Modify the DataMapper class API, rename the methods with shorter names
** Break **: Modify the ResultBuilder class API, rename the methods with shorter names

0.7.1.0-alpha - 2014/12/16 - NEW version:
** New **: Add a method to get the configuration from the DataMapper class

0.7.0.0-alpha - 2014/12/16 - BREAK version:
** New **: Add a class Kassko\DataMapper\DataMapper which contains the interface of Kassko\DataMapper\Result\ResultBuilderFactory and Kassko\DataMapper\Query\QueryFactory
** Break **: The object Kassko\DataMapper\Result\ResultBuilderFactory is removed, use Kassko\DataMapper\DataMapper instead with the same API. Exception the create() method is replaced by createResultBuilder().
** Break **: The class Kassko\DataMapper\Query\QueryFactory is removed, use Kassko\DataMapper\DataMapper instead with the same API.