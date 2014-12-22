0.8.0.0-alpha - 2014/12/22 - Break version:
** Break **: Modify the DataMapper class API, rename the methods with shorter names
** Break **: Modify the ResultBuilder class API, rename the methods with shorter names
** New **: Add a method to get an hydrator from the DataMapper class

0.7.1.0-alpha - 2014/12/16 - NEW version:
** New **: Add a method to get the configuration from the DataMapper class

0.7.0.0-alpha - 2014/12/16 - BREAK version:
** New **: Add a class Kassko\DataMapper\DataMapper which contains the interface of Kassko\DataMapper\Result\ResultBuilderFactory and Kassko\DataMapper\Query\QueryFactory
** Break **: The object Kassko\DataMapper\Result\ResultBuilderFactory is removed, use Kassko\DataMapper\DataMapper instead with the same API. Exception the create() method is replaced by createResultBuilder().
** Break **: The class Kassko\DataMapper\Query\QueryFactory is removed, use Kassko\DataMapper\DataMapper instead with the same API.