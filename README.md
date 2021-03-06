data-mapper
==================

[![Build Status](https://secure.travis-ci.org/kassko/data-mapper.png?branch=0.12)](https://travis-ci.org/kassko/data-mapper)
[![Total Downloads](https://poser.pugx.org/kassko/data-mapper/downloads.png)](https://packagist.org/packages/kassko/data-mapper)

# A php mapper very tunable, cross-orm and cross-DBMS #

* Objects do not extends a base entity class
* Map nested objects
* Support relationship between all types of sources (relational databases, non relational, caches ...)
* Lazy loading
* Supports dynamic configuration with expression language
* Various mapping configuration format
* Can chain some fallbacks sources while a source is unavailable or instable

# Installation #

Note that:
* version contains 4 numbers
* the second version number is used when compatibility is broken
* the third for new feature
* the fourth for hotfix
* the first for new API or to go from pre-release to release (from 0 to 1)

Using a version in `0.12` is recommended. 
Versions in `0.13` are no longer maintained.

Example of good requirement:
```php
composer require kassko/data-mapper:"~0.12.6"
```

# Basic usage #

* [Installation: precisions](#installation-precisions)
* [Accessing datas](#accessing-datas)
  - [Inline data sources](#inline-data-sources)
* [Hydrating your object](#hydrating-your-object)
  - [Hydrating your object using inline data sources](#hydrating-your-object-using-inline-data-sources)
  - [Hydrating your object using a data source store](#hydrating-your-object-using-a-data-source-store)
  - [Working with sources and dependency injection](#working-with-sources-and-dependency-injection)
  - [Example with a relation with a Doctrine source](#example-with-a-relation-with-a-doctrine-source)
  - [Source annotation details](#source-annotation-details)
* [Component configuration reference](#component-configuration-reference)
* [Mapping configuration reference](#mapping-configuration-reference)
  - [Config config](#config-config)
  - [CustomHydrator config](#customhydrator-config)
  - [DataSource config](#datasource-config)
  - [DataSourcesStore config](#datasourcesstore-config)
  - [ExcludeImplicitSource config](#excludeimplicitsource-config)
  - [Field config](#field-config)
  - [Getter config](#getter-config)
  - [Id config](#id-config)
  - [IdCompositePart config](#idcompositepart-config)
  - [Listeners config](#listeners-config)
  - [Method config](#method-config)
  - [Object config](#object-config)
  - [ObjectListeners config - DEPRECATED - SEE Listeners config](#objectlisteners-config---deprecated---see-listeners-config)
  - [PostExtract config - DEPRECATED - SEE Listeners config](#postextract-config---deprecated---see-listeners-config)
  - [PostHydrate config - DEPRECATED - SEE Listeners config](#posthydrate-config---deprecated---see-listeners-config)
  - [PreExtract config - DEPRECATED - SEE Listeners config](#preextract-config---deprecated---see-listeners-config)
  - [PreHydrate config - DEPRECATED - SEE Listeners config](#prehydrate-config---deprecated---see-listeners-config)
  - [Provider config - DEPRECATED - SEE DataSource config](#provider-config---deprecated---see-datasource-config)
  - [ProvidersStore config - DEPRECATED - SEE DataSourcesStore config](#providersstore-config---deprecated---see-datasourcesstore-config)
  - [RefImplicitSource config](#RefImplicitSource-config)
  - [RefSource config](#refsource-config)
  - [Setter config](#setter-config)
  - [ToExclude config](#toexclude-config)
  - [ToInclude config](#toinclude-config)
  - [Transient config](#transient-config)
  - [ValueObject config - DEPRECATED - SEE Config config](#valueobject-config---deprecated---see-config-config)
  - [Version config](#version-config)

# Run tests #

Ensures phpunit is installed
phpunit

### Installation: precisions ###

If you use annotations, register the autoloader:
```php
$loader = require 'vendor/autoload.php';

Doctrine\Common\Annotations\AnnotationRegistry::registerLoader($loader);
```

And run the environment:
```php
(new Kassko\DataMapper\DataMapperBuilder)->run();
```

### Accessing datas ###

#### Data-mapper style ####
```php
$id = 1;
//Construct a person, set an id to it.
$person = new Kassko\Sample\Person($id);

echo $person->getName();//Implicitly load the name from the given id by fetching the source configured.
```

#### Doctrine style ####
```php
//Here some stuff to retrieve the entityManager.

$id = 1;
$person = $entityManager->getRepository('Kassko\Sample\Person')->find($id);

echo $person->getName();
```

With data-mapper, you only need to call a getter, the access logic is in a configuration. 

The configuration is either in the object php file or in a separated file.
If it's in the object file, the configuration is in annotations or a method returns it in a php array or in a yaml string. 
If it's in a separated file, the configuration is in a php or a yaml file.

For simplicity, the usage is documented with annotations format. But in this documentation, you can find reference guide for the others formats.

### Hydrating your object ###

#### Hydrating your object using inline data sources ####

```php
namespace Kassko\Sample;

use Kassko\DataMapper\Annotation as DM;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

class Person
{
    use LoadableTrait;

    private $id;
    /**
     * @DM\DataSource(class="Kassko\Sample\PersonDataSource", method="getData", args="#id", lazyLoading=true)
     */
    private $name;
    /**
     * @DM\DataSource(class="Kassko\Sample\PersonDataSource", method="getData", args="#id", lazyLoading=true)
     */
    private $email;

    public function __construct($id) { $this->id = $id; }
    public function getId() { return $this->id; }
    public function getName() { $this->loadProperty('name'); return $this->name; }
    public function getEmail() { $this->loadProperty('email'); return $this->email; }
}
```

```php
namespace Kassko\Sample;

class PersonDataSource
{
    public function getData($id)
    {
        return [
          'name' => 'foo',
          'email' => 'foo@bar.com'
        ];
    }  
}
```

#### Hydrating your object using a data source store ####

A DataSource store is usefull 

If you prefer to group your sources in one place:

```php
namespace Kassko\Sample;

use Kassko\DataMapper\Annotation as DM;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

/**
 * @DM\DataSourcesStore({
 *      @DM\DataSource(
 *          id="nameSource", 
 *          class="Kassko\Sample\PersonDataSource", 
 *          method="getData",
 *          args="#id",
 *          lazyLoading=true
 *      ),
 *      @DM\DataSource(
 *          id="emailSource", 
 *          class="Kassko\Sample\PersonDataSource", 
 *          method="getData",
 *          args="#id",
 *          lazyLoading=true
 *      )
 * })
 */
class Person
{
    use LoadableTrait;

    private $id;
    /**
     * @DM\RefSource(id="nameSource")
     */
    private $name;
    /**
     * @DM\RefSource(id="emailSource")
     */
    private $email;

    public function __construct($id) { $this->id = $id; }
    public function getId() { return $this->id; }
    public function getName() { $this->loadProperty('name'); return $this->name; }
    public function getEmail() { $this->loadProperty('email'); return $this->email; }
}
```

Or if you have a source which hydrate several properties:

```php
namespace Kassko\Sample;

use Kassko\DataMapper\Annotation as DM;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

/**
 * @DM\DataSourcesStore({
 *      @DM\DataSource(
 *          id="personSource", 
 *          class="Kassko\Sample\PersonDataSource", 
 *          method="getData",
 *          args="#id", 
 *          lazyLoading=true,
 *          supplySeveralFields=true
 *      )
 * })
 */
class Person
{
    use LoadableTrait;

    private $id;
    /**
     * @DM\RefSource(id="personSource")
     * @DM\Field("name"="first_name")
     */
    private $firstName;
    /**
     * @DM\RefSource(id="personSource")
     */
    private $name;
    /**
     * @DM\RefSource(id="personSource")
     */
    private $email;
    /**
     * @DM\RefSource(id="personSource")
     */
    private $phone;

    public function __construct($id) { $this->id = $id; }
    public function getId() { return $this->id; }
    public function getFirstName() { $this->loadProperty('firstName'); return $this->firstName; }
    public function getName() { $this->loadProperty('name'); return $this->name; }
    public function getEmail() { $this->loadProperty('email'); return $this->email; }
    public function getPhone() { $this->loadProperty('phone'); return $this->phone; }
}
```

```php
namespace Kassko\Sample;

class PersonDataSource
{
    public function getData($id)
    {
        return [
            'first_name' => 'foo',
            'name' => 'bar',
            'email' => 'foo@bar',
            'phone' => '01 02 03 04 05',
        ];
    }
}
```

Note that you don't have to expose the setters. Expose them only if you have specific logic to write. I advise you not to make "public" these setters since the properties they wrapp are loaded (and so managed) with a data source.


#### Working with sources and dependency injection

CarRepository has some dependencies too (the entity manager), it is instantiated with a resolver class-resolver. Reminder: you can see more details [here](#work-with-object-complex-to-create-like-service).

Run environment and register dependencies:
```php
(new Kassko\DataMapper\DataMapperBuilder)
    ->settings(['container' => ['instance' => ['person.data_source_id' => $personDataSourceInstance]]])
    ->run()
;
```

You can send an object which implements `\ArrayAccess`:
```php
(new Kassko\DataMapper\DataMapperBuilder)
    ->settings(['container' => ['instance' => $container]])
    ->run()
;
```

Or send an object which have "get" and "has" methods:
```php
(new Kassko\DataMapper\DataMapperBuilder)
    ->settings(['container' => ['instance' => $container, 'get_method_name' => 'get', 'has_method_name' => 'has']])
    ->run()
;
```

And you specify your dependency id like below:

```php
/**
 * @DM\DataSourcesStore({
 *      @DM\DataSource(
 *          id="personSource", 
 *          class="@person.data_source_id", 
 *          method="find", 
 *          args="#id",
 *          lazyLoading=true
 *      )
 * })
 */
class Person
{
}
```

#### Source annotation details ####
* `id`. An arbitrary id for the source. Optional but necessary if you need to mentionned the source in another annotation.
* `class`. The class of the source that return datas. If the source has some dependencies (above `PersonDataSource` has a dependency `$connection`), its instanciation can be performed by a resolver named class-resolver. See more details [here](#work-with-object-complex-to-create-like-service).
* `method`. The name of the method that return datas. 
* `args`. Arguments of the method that return datas. You can send a raw value, a field value with the prefix `#` (ex: `#id`), an expression and more ... See more details [here](#method-arguments)
* `supplySeveralFields`. Whether the source returns the data directly or put them in a key of an array and return the array. If the source supply only one field, it can return directly the data, these data will be bound to the good property. Else the source should return an array with as keys as property to supply. The key is named like the property or a mapping is done in the annotation Field. 


Data-mapper is not an ORM so it cannot generate for you some sql statement. But it can wrapp the use of an ORM like Doctrine ORM so that you can take advantage of the two. It also can help you to make relations between different DBMS.

#### Example with a relation with a Doctrine source ####
```php
namespace Kassko\Sample;

use Kassko\DataMapper\ObjectExtension\LoadableTrait;

/**
 * @DM\DataSourcesStore({
 *      @DM\DataSource(
 *          id="personSource", 
 *          class="Kassko\Sample\PersonDataSource", 
 *          method="getData", 
 *          args="#id", 
 *          lazyLoading=true,
 *          supplySeveralFields=true
 *      ),
 *      @DM\DataSource(
 *          id="carSource", 
 *          class="Kassko\Sample\CarRepository", 
 *          method="find", 
 *          args="expr(source('personSource')['car_id'])",
 *          lazyLoading=true
 *      )
 * })
 */
class Person
{
    private $id;
    /**
     * @DM\RefSource(id="personSource")
     */
    private $firstName;
    /**
     * @DM\RefSource(id="personSource")
     */
    private $name;
    /**
     * @DM\RefSource(id="personSource")
     */
    private $email;
    /**
     * @DM\RefSource(id="personSource")
     */
    private $phone;
    /**
     * @DM\RefSource(id="carSource")
     */
    private $car;

    public function __construct($id) { $this->id = $id; }
    public function getId() { return $this->id; }
    public function getFirstName() { return $this->firstName; }
    public function getName() { return $this->name; }
    public function getEmail() { return $this->email; }
    public function getPhone() { return $this->phone; }
    public function getCar() { return $this->car; }
}
```

```php
namespace Kassko\Sample;

class PersonDataSource
{
    public function getData($id)
    {
        return [
            'first_name' => 'foo',
            'name' => 'bar',
            'email' => 'foo@bar',
            'phone' => '01 02 03 04 05',
        ];
    }
}
```

```php
namespace Kassko\Sample;

use Doctrine\ORM\EntityRepository;

/**
 * CarRepository is a Doctrine source that feed the property $car.
 */
class CarRepository extends EntityRepository
{
}
```

# Features: details #

* [Basic usage](#basic-usage)
* [Use the Result builder](#use-the-result-builder)
* [Enforce type of fields](#enforce-type-of-fields)
* [Apply converters before hydration or extraction](#apply-converters-before-hydration-or-extraction)
  - [Converter](#converter)
  - [Date converter](#date-converter)
* [Add callbacks before or after hydration process](#add-callbacks-before-or-after-hydration-process)
* [Customize getters and setters](#customize-getters-and-setters)
* [Hydrate nested objects](#hydrate-nested-objects)
* [Configure a php object hydrator instead of using a mapping configuration](#configure-a-php-object-hydrator-instead-of-using-a-mapping-configuration)
* [Work with object complex to create like service](#work-with-object-complex-to-create-like-service)
* [Work with other mapping configuration format](#work-with-other-mapping-configuration-format)
  - [Outer mapping configuration format](#outer-mapping-configuration-format)
  - [Inner mapping configuration format](#inner-mapping-configuration-format)
* [Improve persistance ignorance](#improve-persistance-ignorance)
* [Choose a mapping configuration at runtime](#choose-a-mapping-configuration-at-runtime)
* [Bind a mapping configuration to a property especially](#bind-a-mapping-configuration-to-a-property-especially)
* [Bind a source to a property or a set of properties / hydrate object from multi-sources, multi-orm](#bind-a-source-to-a-property-or-a-set-of-properties-hydrate-object-from-multi-sources-multi-orm)
  - [Data source](#data-source)
  - [Method arguments](#method-arguments)
  - [Lazy loading](#lazy-loading)
  - [Source store](#source-store)
  - [Fallback source](#fallback-source)
  - [Processor](#processor)
  - [Depends](#depends)
* [Work with relations](#work-with-relations)
* [How to have DataMapper/ResultBuilder ignorance in the client code](#how-to-have-datamapper-resultbuilder-ignorance-in-the-client-code)
* [Use expression language](#use-expression-language)
  - [Basic usage of expression language](#basic-usage-of-expression-language)
  - [Add a function provider](#add-a-function-provider)
* [Object listener](#object-listener)
* [Add a custom mapping configuration format](#add-a-custom-mapping-configuration-format)
* [Inherit mapping configuration](#inherit-mapping-configuration)

=======================================================================================================

Basic usage
-----------

Given an object:

```php
namespace Kassko\Sample;

class Watch
{
        private $brand;
        private $color;

        public function getBrand() { return $this->brand; }
        public function setBrand($brand) { $this->brand = $brand; }
        public function getColor() { return $this->color; }
        public function setColor($color) { $this->color = $color; }
}
```

Use the ResultBuilder:

The result builder allows you to hydrate all a collection from a result that contains several records. It also allows you to get only one result even if the result contains several records.
```php
$data = [
        0 => [
                'brand' => 'some brand',
                'color' => 'blue',
        ],
        1 => [
                'brand' => 'some other brand',
                'color' => 'green',
        ],
];

$dataMapper = (new Kassko\DataMapper\DataMapperBuilder)->instance();

$dataMapper->resultBuilder('Kassko\Sample\Watch', $data)->all();//The result will be an array with two objects.
$dataMapper->resultBuilder('Kassko\Sample\Watch', $data)->first();//The result will be a watch object representing the first record.
```

The code above will display:
```php
array(2) {
    ["brand"]=>
    string(10) "some brand"
    ["color"]=>
    string(4) "blue"
}
```

Inversely, you can extract values of an object or of an object collection:

```php
$dataMapper = (new Kassko\DataMapper\DataMapperBuilder)->instance();

$dataMapper->resultBuilder('Kassko\Sample\Watch')->raw($object);
$dataMapper->resultBuilder('Kassko\Sample\Watch')->raw($collection);
```

Above, we use the default hydrator. But you often need to customize hydration because column names are different of properties name and for many other reasons.

To customize the hydration you should provide a mapping configuration. Severals formats are available but in this documentation we choose to use the annotation format (more details about all mapping configuration format are available [`here`](https://github.com/kassko/data-mapper/blob/master/Resources/doc/mapping_format.md)).

```php
namespace Kassko\Sample;

use Kassko\DataMapper\Annotation as DM;

class Watch
{
        private $brand;

        /**
         * @DM\Fields(name="COLOR")
         */
        private $color;

        public function getBrand() { return $this->brand; }
        public function setBrand($brand) { $this->brand = $brand; }
        public function getColor() { return $this->color; }
        public function setColor($color) { $this->color = $color; }
}
```

```php
$loader = require 'vendor/autoload.php';

Doctrine\Common\Annotations\AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

$dataMapper = (new Kassko\DataMapper\DataMapperBuilder)->instance();
$dataMapper->resultBuilder('Kassko\Sample\Watch', $data)->all();
```

Use the result builder
-----------

You can find below all the ways to get results with the ResultBuilder:

```php
        /*
        Return an array of objects.
        So return an array with only one object, if only one fullfill the request.
        */
        $resultBuilder->all();
```

```php
        /*
        Return the object found.

        If more than one result are found, throw an exception
        Kassko\DataMapper\Result\Exception\NonUniqueResultException.

        If no result found, throw an exception
        Kassko\DataMapper\Result\Exception\NoResultException.
        */
        $resultBuilder->single();
```

```php
        /*
        Return the object found or null.
        */
        $resultBuilder->one();

        /*
        Return the object found or a default result (like false).
        */
        $resultBuilder->one(false);

        /*
        If more than one result are found, throw an exception
        Kassko\DataMapper\Result\Exception\NonUniqueResultException.
        */
```

```php
        /*
        Return the first object found or null.
        */
        $resultBuilder->first();

        /*
        Return the first object found or a default result (like value false).
        */
        $resultBuilder->first(false);

        /*
        If no result found, throw an exception
        Kassko\DataMapper\Result\Exception\NoResultException.
        */
```

```php
        /*
        Return an array indexed by a property value.

        If the index does not exists (allIndexedByUnknown()), throw an exception Kassko\DataMapper\Result\Exception\NotFoundIndexException.

        If the same index is found twice, throw an exception
        Kassko\DataMapper\Result\Exception\DuplicatedIndexException.

        Examples:

        allIndexedByBrand() will index by brand value:
        [
                'BrandA' => $theWatchInstanceWithBrandA,
                'BrandB' => $theWatchInstanceWithBrandB,
        ]

        allIndexedByColor() will index by color value:
        [
                'Blue' => $theWatchInstanceWithColorBlue,
                'Red' => $theWatchInstanceWithColorRed,
        ]

        allIndexedByUnknown() will throw a Kassko\DataMapper\Result\Exception\NotFoundIndexException.
        */
        $resultBuilder->allIndexedByBrand();//Indexed by brand value
        //or
        $resultBuilder->allIndexedByColor();//Indexed by color value
```

```php
        /*
        Return an iterator.

        Result will not be hydrated immediately but only when you will iterate the results (with "foreach" for example).
        */
        $result = $resultBuilder->iterable();
        foreach ($result as $object) {//$object is hydrated

                if ($object->getColor() === 'blue') {
                        break;

                        //We found the good object then we stop the loop and others objects in results will not be hydrated.
                }
        }
```

```php
        /*
        Return an iterator indexed by a property value.

        If the index does not exists, throw an exception Kassko\DataMapper\Result\Exception\NotFoundIndexException.

        If the same index is found twice, throw an exception Kassko\DataMapper\Result\Exception\DuplicatedIndexException.
        */
        $resultBuilder->iterableIndexedByBrand();
        //or
        $resultBuilder->iterableIndexedByColor();
```

Enforce type of fields
-----------
This section will be written later.

Apply converters before hydration or extraction
-----------

### Converter
```php
namespace Kassko\Sample;

use Kassko\DataMapper\Annotation as DM;
use Kassko\DataMapper\Hydrator\HydrationContextInterface;
use Kassko\DataMapper\Hydrator\Value;
use \DateTime;

class Watch
{
        private static $brandCodeToLabelMap = [1 => 'Brand A', 2 => 'Brand B'];
        private static $brandLabelToCodeMap = ['Brand A' => 1, 'Brand B' => 2];

        /**
         * @DM\Field(readConverter="readBrand", writeConverter="writeBrand")
         */
        private $brand;

        /**
         * @DM\Field(readConverter="hydrateBool", writeConverter="extractBool")
         */
        private $waterProof;

        /**
         * @DM\Field(readConverter="hydrateBoolFromSymbol", writeConverter="extractBoolToSymbol")
         */
        private $stopWatch;

        /**
         * @DM\Field(type="date", readDateConverter="Y-m-d H:i:s", writeDateConverter="Y-m-d H:i:s")
         */
        private $createdDate;

        public function getBrand() { return $this->brand; }
        public function setBrand($brand) { $this->brand = $brand; }
        public function isWaterProof() { return $this->waterProof; }
        public function setWaterProof($waterProof) { $this->waterProof = $waterProof; }
        public function hasStopWatch() { return $this->stopWatch; }
        public function setStopWatch($stopWatch) { $this->stopWatch = $stopWatch; }
        public function getCreatedDate() { return $this->createdDate; }
        public function setCreatedDate(DateTime $createdDate) { $this->createdDate = $createdDate; }

        public static function readBrand(Value $value, HydrationContextInterface $context)
        {
                if (isset(self::$brandCodeToLabelMap[$value->value])) {
                        $value->value = self::$brandCodeToLabelMap[$value->value];
                }
        }

        public static function writeBrand(Value $value, HydrationContextInterface $context)
        {
                if (isset(self::$brandLabelToCodeMap[$value->value])) {
                        $value->value = self::$brandLabelToCodeMap[$value->value];
                }
        }

        public static function hydrateBool(Value $value, HydrationContextInterface $context)
        {
                $value->value = $value->value == '1';
        }

        public static function extractBool(Value $value, HydrationContextInterface $context)
        {
                $value->value = $value->value ? '1' : '0';
        }

        public static function hydrateBoolFromSymbol(Value $value, HydrationContextInterface $context)
        {
                $value->value = $value->value == 'X';
        }

        public static function extractBoolToSymbol(Value $value, HydrationContextInterface $context)
        {
                $value->value = $value->value ? 'X' : ' ';
        }
}
```

readDateConverter contains the format of the string to transform into Date object. Internally, DataMapper uses the Php function DateTime::createFromFormat() to create a Date object from a raw string.

writeDateConverter contains the string format in which you want to serialize your Date object. Internally, DataMapper uses the Php function DateTime::createFromFormat() to serialise the Date object in a string.

Given this code:
```php
$data = [
        'brand' => '1',
        'waterProof' => '1',
        'stopWatch' => 'X',
        'created_date' => '2014-09-14 12:36:52',
];

$dataMapper = (new Kassko\DataMapper\DataMapperBuilder)->instance();
$object = new Kassko\Sample\Watch;
$dataMapper->hydrator('Kassko\Sample\Watch')->hydrate($data, $object);
var_dump($object);
```

Your output will be:
```php
object(Watch)#283 (8) {
        ["brand":"Watch":private]=> string(10) "Brand A"
        ["waterProof":"Watch":private]=> bool(true)
        ["stopWatch":"Watch":private]=> bool(true)
        ["createdDate":"Watch":private]=>
                object(DateTime)#320 (3) { ["date"]=> string(19) "2014-09-14 12:36:52" ["timezone_type"]=> int(3) ["timezone"]=> string(13) "Europe/Berlin" }
}
```

If the created_date had a bad format, an exception would have been thrown.
For example, the format given above in the read date converter is 'Y-m-d H:i:s', so a create_date like '2014 09 14 12h36m52s' is not correct.

### Date converter
This section will be written later.

Add callbacks before or after hydration process
-----------
This section will be written later.

Customize getters and setters
-----------

DataMapper automatically recognize getter (or isser or haser) and setter of a field.

```php
namespace Kassko\Sample;

use Kassko\DataMapper\Annotation as DM;

class Watch
{
        /**
         * @DM\Field
         */
        private $brand;

        /**
         * @DM\Field
         */
        private $waterProof;

        /**
         * @DM\Field
         */
        private $stopWatch;

        public function getBrand() { return $this->brand; }
        public function setBrand($brand) { $this->brand = $brand; }
        public function isWaterProof() { return $this->waterProof; }
        public function setWaterProof($waterProof) { $this->waterProof = $waterProof; }
        public function hasStopWatch() { return $this->stopWatch; }
        public function setStopWatch($stopWatch) { $this->stopWatch = $stopWatch; }
}
```

To retrieve the corresponding getter, DataMapper look in order at:
* a getter
* an isser
* a haser

You also can specify corresponding getters/setters:

```php
namespace Kassko\Sample;

use Kassko\DataMapper\Annotation as DM;

class Watch
{
        /**
         * @DM\Field(prefix="is")
         */
        private $waterProof;

        /**
         * @DM\Getter(prefix="has")
         */
        private $alarm;

        /**
         * @DM\Getter(prefix="are")
         */
        private $handsYellow;

        /**
         * @DM\Field(name="hasStopWatch")
         */
        private $stopWatch;

        /**
         * @DM\Getter(name="canColorChange")
         * @DM\Setter(name="colorCanChange")
         */
        private $variableColor;

        public function isWaterProof() { return $this->waterProof; }
        public function setWaterProof($waterProof) { $this->waterProof = $waterProof; }
        public function hasAlarm() { return $this->alarm; }
        public function setAlarm($stopWatch) { $this->alarm = $alarm; }
        public function areHandsYellow() { return $this->handsYellow; }
        public function setHandsyellow($handsYellow) { $this->handsYellow = $handsYellow; }
        public function hasStopWatch() { return $this->stopWatch; }
        public function setStopWatch($stopWatch) { $this->stopWatch = $stopWatch; }
        public function canColorChange() { return $this->variableColor; }
        public function colorCanChange($colorCanChange) { $this->variableColor = $colorCanChange; }
}
```

Hydrate nested objects
-----------
This section will be written later.

Configure a php object hydrator instead of using a mapping configuration
-----------
This section will be written later.

Work with object complex to create, like service
-----------
This section will be written later.

Work with other mapping configuration format
-----------
This section will be written later.


### Outer mapping configuration format
This section will be written later.

### Inner mapping configuration format
This section will be written later.

Improve persistance ignorance
-----------
This section will be written later.

Choose a mapping configuration at runtime
-----------

You can use the same model with various mapping configuration but you must work with one of the outer mapping configuration and not with mapping embedded in the object. So 'yaml_file' or 'php_file' are correct mapping format but 'annotations', 'inner_php' or 'inner_yaml' are bad format.

```php
namespace Kassko\Sample;

class Color
{
        private $red;
        private $green;
        private $blue;

        public function getRed() { return $this->red; }
        public function setRed($red) { $this->red = $red; }
        public function getGreen() { return $this->green; }
        public function setGreen($green) { $this->green = $green; }
        public function getBlue() { return $this->blue; }
        public function setBlue($blue) { $this->blue = $blue; }
}
```

A english data source with the mapping in yaml:
```yaml
# color_en.yml

fields:
        red: ~
        green: ~
        blue: ~
```

A french data source with the mapping in yaml:
```yaml
# color_fr.yml

fields:
        red:
                name: rouge
        green:
                name: vert
        blue:
                name: bleu
```

And imagine we've got a spanish data source with the mapping in a php format.
```php
//color_es.php

return [
        'fields' => [
                'red' => 'rojo',
                'green' => 'verde',
                'blue' => 'azul',
        ],
];
```

```php
use DataMapper\Configuration\RuntimeConfiguration;

$data = [
        'red' => '255',
        'green' => '0',
        'blue' => '127',
];

$resultBuilder = $dataMapper->resultBuilder('Kassko\Sample\Color', $data);
$resultBuilder->setRuntimeConfiguration(
        (new RuntimeConfiguration)
        ->addClassMetadataDir('Color', 'some_resource_dir')//Optional, if not specified Configuration::defaultClassMetadataResourceDir is used.
        ->addMappingResourceInfo('Color', 'color_en.yml', 'inner_yaml')
);

$resultBuilder->single();
```

```php
use DataMapper\Configuration\RuntimeConfiguration;

$data = [
        'rouge' => '255',
        'vert' => '0',
        'bleu' => '127',
];

$resultBuilder = $dataMapper->resultBuilder('Kassko\Sample\Color', $data);
$resultBuilder->setRuntimeConfiguration(
        (new RuntimeConfiguration)
        ->addClassMetadataDir('Color', 'some_resource_dir')
        ->addMappingResourceInfo('Color', 'color_fr.yml', 'inner_yaml')
);

$resultBuilder->single();
```

```php
use DataMapper\Configuration\RuntimeConfiguration;

$data = [
        'rojo' => '255',
        'verde' => '0',
        'azul' => '127',
];

$resultBuilder = $dataMapper->resultBuilder('Kassko\Sample\Color', $data);
$resultBuilder->setRuntimeConfiguration(
        (new RuntimeConfiguration)
        ->addClassMetadataDir('Color', 'some_resource_dir')
        ->addMappingResourceInfo('Color', 'color_es.php', 'inner_php')
);

$resultBuilder->single();
```

Bind a mapping configuration to a property especially
-----------

```php
namespace Kassko\Sample;

use Kassko\DataMapper\Annotation as DM;

class Customer
{
        /**
         * @DM\Field
         * @DM\Id
         */
        private $id;

        /**
         * @DM\Field(class="Kassko\Sample\Address")
         * @DM\Config(mappingResourceType="yaml", mappingResourceName="billing_address.yml")
         */
        private $billingAddress;//$billingAddress is a value object.

        /**
         * @DM\Field(class="Kassko\Sample\Address")
         * @DM\Config(mappingResourceType="yaml", mappingResourceName="shipping_address.yml")
         */
        private $shippingAddress;//$shippingAddress is a value object too.
}
```

```php
namespace Kassko\Sample;

class Address
{
        private $street;
        private $town;
        private $postalCode;
        private $country;
}
```

```yaml
# billing_address.yml

fields:
        street:
                name: billing_street
        town:
                name: billing_town
        postalCode:
                name: billing_postal_code
        country:
                name: billing_country
```

```yaml
# shipping_address.yml

fields:
        street:
                name: shipping_street
        town:
                name: shipping_town
        postalCode:
                name: shipping_postal_code
        country:
                name: shipping_country
```

```php

$data = [
        'id' => 1,
        'billing_street' => '12 smarties street',
        'billing_town' => 'Nuts',
        'billing_postal_code' => '654321'
        'billing_country' => 'England',
        'shipping_street' => '23 smarties street',
        'shipping_town' => 'Mars',
        'shipping_postal_code' => '987654'
        'shipping_country' => 'England',
];

$dataMapper->resultBuilder('Kassko\Sample\Customer', $data)->single();
```
Note that you can have value objects which contains value objects and so on. And each value object can use it's own mapping configuration format.

Bind a source to a property or a set of properties / hydrate object from multi-sources, multi-orm
-----------

### Data source

```php
namespace Kassko\Sample;

use Kassko\DataMapper\Annotation as DM;

class Information
{
    /**
     * @DM\DataSource(class="Kassko\Sample\ShopDataSource", method="getBestShop")
     * @DM\Field(class='Kassko\Sample\Shop')
     */
    private $bestShop;

    /**
     * @DM\DataSource(class="Kassko\Sample\ShopDataSource", method="getNbShops")
     */
    private $nbShops;

    public function getBestShop() { return $this->bestShop; }
    public function setBestShop(Shop $shop) { $this->bestShop = $bestShop; }
}
```

```php
namespace Kassko\Sample;

use Kassko\DataMapper\Annotation as DM;

class Shop
{
    /**
     * @DM\Field
     */
    private $name;

    /**
     * @DM\Field
     */
    private $address;
}
```

```php
namespace Kassko\Sample;

class ShopDataSource
{
    public function getBestShop()
    {
        return [
            'name' => 'The best',
            'address' => 'Street of the bests',
        ];
    }

    public function getNbShops()
    {
        return 25;
    }
}
```

### Method arguments
This section will be written later.

### Lazy loading

Below, we load properties "bestShop" and "keyboard" only when we use it.

```php
namespace Kassko\Sample;

use Kassko\DataMapper\Annotation as DM;
use Kassko\DataMapper\ObjectExtension\LazyLoadableTrait;

class Information
{
    use LazyLoadableTrait;

        /**
         * @DM\DataSource(class="Kassko\Sample\ShopDataSource", method="getBestShop", lazyLoading=true)
         * @DM\Field(class='Kassko\Sample\Shop')
         */
        private $bestShop;

        /**
         * @DM\DataSource(class="Kassko\Sample\ShopDataSource", method="getNbShops", lazyLoading=true)
         */
        private $nbShops;

        public function getBestShop()
        {
                $this->loadProperty('bestShop');//<= Load the best shop from the property name if not loaded.
                return $this->bestShop;
        }

        public function getNbShop()
        {
                $this->loadProperty('nbShops');//<= Load the best shop from the property name if not loaded.
                return $this->nbShops;
        }
}
```

```php
namespace Kassko\Sample;

use Kassko\DataMapper\Annotation as DM;

class Shop
{
        /**
         * @DM\Field
         */
        private $name;

        /**
         * @DM\Field
         */
        private $address;
}
```

```php
namespace Kassko\Sample;

class ShopDataSource
{
    public function getBestShop()
    {
        return [
            'name' => 'The best',
            'address' => 'Street of the bests',
        ];
    }

    public function getNbShops()
    {
        return 25;
    }
}
```

### Source store

Given this code:

```php
namespace Kassko\Sample;

use Kassko\DataMapper\Annotation as DM;

class Person
{
    /**
     * @DM\DataSource(class="Kassko\Sample\PersonDataSource", method="getData", lazyLoading=true, supplySeveralFields=true)
     */
    private $name;

    /**
     * @DM\DataSource(class="Kassko\Sample\PersonDataSource", method="getData", lazyLoading=true, supplySeveralFields=true)
     */
    private $address;

    /**
     * @DM\DataSource(class="Kassko\Sample\PersonDataSource", method="getData", lazyLoading=true, supplySeveralFields=true)
     */
    private $phone;

    /**
     * @DM\DataSource(class="Kassko\Sample\PersonDataSource", method="getData", lazyLoading=true, supplySeveralFields=true)
     */
    private $email;
}
```

```php
namespace Kassko\Sample;

class PersonDataSource
{
    public function getData()
    {
        return [
            'name' => 'Foo',
            'address' => 'Blue road',
            'phone' => '01 02 03 04 05',
            'email' => 'foo@bar.baz',
        ];
    }
}
```

We can remove the duplication:

```php
namespace Kassko\Sample;

use Kassko\DataMapper\Annotation as DM;

/**
 * @DM\DataSourcesStore({
 *      @DM\DataSource(
 *          id="some_source", class="Kassko\Sample\PersonDataSource", method="getData", lazyLoading=true
 *      )
 * })
 */
class Person
{
    /**
     * @DM\RefSource(id="some_source")
     */
    private $name;

    /**
     * @DM\RefSource(id="some_source")
     */
    private $address;

    /**
     * @DM\RefSource(id="some_source")
     */
    private $phone;

    /**
     * @DM\RefSource(id="some_source")
     */
    private $email;
}
```

### Fallback source

Here, sourceB replace sourceA if its not stable:

```php
namespace Kassko\Sample;

use Kassko\DataMapper\Annotation as DM;

/**
 * @DM\DataSourcesStore({
 *      @DM\DataSource(
 *          id="sourceA", class="Kassko\Sample\ShopDataSource", method="getData", lazyLoading=true,
 *          fallbackSourceId="sourceB", onFail="checkException", exceptionClass="Kassko\Sample\NotStableSourceException"
 *      )
 * })
 */
class Person
{
}
```

Or:

```php
namespace Kassko\Sample;

use Kassko\DataMapper\Annotation as DM;

/**
 * @DM\DataSourcesStore({
 *      @DM\DataSource(
 *          id="sourceA", class="Kassko\Sample\ShopDataSource", method="getData", lazyLoading=true,
 *          fallbackSourceId="sourceB", onFail="checkReturnValue", badReturnValue="null"
 *      )
 * })
 */
class Person
{
}
```
Bad return values could be: `"null"`, `"false"`, `"emptyString"` or `"emptyArray"`.

### Processor

```php
namespace Kassko\Sample;

use Kassko\DataMapper\Annotation as DM;

class Key
{
    use LazyLoadableTrait;

    private $note;
    private $octave = 3;

    /**
     * @DM\DataSource(
     * id="ida", 
     * class="Kassko\Samples\KeyLLManager", 
     * method="getData", supplySeveralFields=true,
     * preprocessors = @DM\Methods({
     *  @DM\Method(method="somePreprocessor"),
     *  @DM\Method(class="Kassko\Sample\KeyProcessor", method="preprocess", args="##this")
     * })
     * processors = @DM\Methods({
     *  @DM\Method(method="someProcessor"),
     *  @DM\Method(class="Kassko\Sample\KeyProcessor", method="process", args="##this")
     * })
     *)
     */
    public $color;

    public function getColor() 
    {
        $this->loadProperty('color');
        return $this->color;
    }

    public function somePrepocessor()
    {
        //Some stuff
    }

    public function someProcessor())
    {
        //Some stuff
    }
}
```

```php
namespace Kassko\Sample;

class KeyPreprocessor
{
    public function preprocess(Key $key)
    {
        $this->logger->info(sprintf('Before key hydration %s', $key->getId()));
    }

    public function process($keyColor)
    {
        $this->logger->info(sprintf('After key hydration %s', $key->getId()));
    }
}
```

Or:
```php
class Key
{
    use LazyLoadableTrait;

    private $note;
    private $octave = 3;

    /**
     * @DM\DataSource(
     * id="ida", 
     * class="Kassko\Samples\KeyLLManager", 
     * method="getData", supplySeveralFields=true,
     * preprocessor = @DM\Method(method="somePreprocessor"),  
     * processor = @DM\Method(method="someProcessor")
     *)
     */
    public $color;

    public function getColor() 
    {
        $this->loadProperty('color');
        return $this->color;
    }

    public function somePrepocessor()
    {
        //Some stuff
    }

    public function someProcessor())
    {
        //Some stuff
    }
}
```
### Depends

`depends` contains sources of which depends an other source. It's usefull when you need to ensures that a property is already available in a zone.

This section will be completed later.

Work with relations
-----------
This section will be written later.

How to have DataMapper/ResultBuilder ignorance in the client code
-----------
This section will be written later.

Use expression language
-----------
This section will be written later.

### Basic usage of expression language
This section will be written later.

### Add a function provider
This section will be written later.

Object listener
-----------
This section will be written later.

Add a custom mapping configuration format
-----------
This section will be written later.

Inherit mapping configuration
-----------
This section will be written later.

Component configuration reference
-----------

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

Mapping configuration reference
-----------

### Config config

Annotation format:
```php
use Kassko\DataMapper\Annotation as DM;

class SomeClass
{
    /**
     * @DM\Config(
     *      class="\ValueObjectClass",
     *      mappingResourceName="valueObjectResourceName",
     *      mappingResourcePath="valueObjectResourcePath",
     *      mappingResourceType="valueObjectResourceType"
     * )
     */
    protected $firstField;
```

Yaml format:
```yml
fields: [firstField]
config:
    firstField:
        class: "\\\ValueObjectClass"
        mappingResourceName: valueObjectResourceName
        mappingResourcePath: valueObjectResourcePath
        mappingResourceType: valueObjectResourceType
```

Php format:
```php
[
    'fields' => ['firstField'],
    'config' => [
        'firstField'    => [
            'class' => '\ValueObjectClass',
            'mappingResourceName' => 'valueObjectResourceName',
            'mappingResourcePath' => 'valueObjectResourcePath',
            'mappingResourceType' => 'valueObjectResourceType'
        ]
    ]
];
```

### CustomHydrator config

Annotation format:
```php
use Kassko\DataMapper\Annotation as DM;

/**
 * Class SomeClass
 * 
 * @DM\CustomHydrator(
 *      class="SomeClassHydrator",
 *      hydrateMethod="hydrateMethod",
 *      extractMethod="extractMethod"
 * )
 */
class SomeClass
{
}
```

Yaml format:
```yml
object:
  customHydrator:
    class: SomeClassHydrator
    hydrateMethod: hydrateMethod
    extractMethod: extractMethod
```

Php format:
```php
[
    'object'    => [
        'customHydrator'    => [
            'class' => 'SomeClassHydrator',
            'hydrateMethod' => 'hydrateMethod',
            'extractMethod' => 'extractMethod'
        ]
    ]
];
```

The methods prototype:
```php
class SomeClassHydrator
{
    /**
     * Hydrate a SomeClass object from raw data $data.
     *
     * @param array The raw data
     * @return SomeClass
     */
    public function hydrateMethod(array $data)
    {
    }

    /**
     * Extract data from a SomeClass instance $object.
     *
     * @param SomeClass The object
     * @return array
     */
    public function extractMethod(SomeClass $object)
    {
    }
}
```

### DataSource config

Annotation format:
```php
use Kassko\DataMapper\Annotation as DM;

class SomeClass
{
    /**
     * @DM\DataSource(
     *      id="firstFieldId",
     *      lazyLoading=true,
     *      supplySeveralFields=true,
     *      depends={"depend#1","depend#2"},
     *      onFail="checkException",
     *      exceptionClass="\RuntimeException",
     *      badReturnValue="emptyString",
     *      fallbackSourceId="firstFieldFallbackSourceId",
     *      preprocessor=@DM\Method(method="fooPreprocessor"),
     *      processors = @DM\Methods({
     *          @DM\Method(method="barProcessor"),
     *          @DM\Method(method="bazProcessor")
     *      })
     *      class="\stdClass",
     *      method="someMethod",
     *      args={"argument#1", "argument#2"}
     * )
     */
     protected $firstField;
}
```

Yaml format:
```yml
fields:
    firstField:
        name: originalFieldName
        dataSource:
            id: firstFieldId
            lazyLoading: true
            supplySeveralFields: true
            depends: [depend#1, depend#2]
            onFail: checkException
            exceptionClass: "\\\RuntimeException"
            badReturnValue: emptyString
            fallbackSourceId: firstFieldFallbackSourceId
            preprocessor:
                class: "##this"
                method: fooPreprocessor
                args: []
            processor: ~
            preprocessors: []
            processors: 
                - {method: barProcessor}
                - {method: bazProcessor}
            class: "\\\stdClass"
            method: someMethod
            args: [argument#1, argument#2]
```

Php format:
```php
[
    'fields' => [
        'firstField' => [
            'name'       => 'originalFieldName',
            'dataSource' => [
                'id'                  => 'firstFieldId',
                'lazyLoading'         => true,
                'supplySeveralFields' => true,
                'depends'             => ['depend#1', 'depend#2'],
                'onFail'              => 'checkException',
                'exceptionClass'      => '\RuntimeException',
                'badReturnValue'      => 'emptyString',
                'fallbackSourceId'    => 'firstFieldFallbackSourceId',
                'preprocessor'        => [
                    'class'  => '##this',
                    'method' => 'fooPreprocessor',
                    'args'   => []
                ],
                'processor'           => [],
                'preprocessors'       => [],
                'processors'          => [
                    ['method' => 'barProcessor'],
                    ['method' => 'bazProcessor'],
                ],
                'class'               => '\stdClass',
                'method'              => 'someMethod',
                'args'                => ['argument#1', 'argument#2']
            ]
        ]
    ]
];
```

To know more about the "Method config" usage, please see its dedicated documentation ["Method config"](#method-config).

### DataSourcesStore config

Annotation format:
```php
use Kassko\DataMapper\Annotation as DM;

/**
 * @DM\DataSourcesStore({
 *      @DM\DataSource(
 *          id="personSource",
 *          class="Kassko\Sample\PersonDataSource",
 *          method="getData",
 *          args="#id",
 *          lazyLoading=true,
 *          supplySeveralFields=true,
 *          onFail="checkException",
 *          exceptionClass="\RuntimeException",
 *          badReturnValue="emptyString",
 *          fallbackSourceId="testFallbackSourceId",
 *          depends="#dependsFirst",
 *          preprocessor = @DM\Method(method="somePreprocessor"),
 *          processor = @DM\Method(method="someProcessor")
 *      )
 * })
 */
class SomeClass
{
}
```

Yaml format:
```yml
object:
    dataSourcesStore:
        - id: personSource
        class: "Kassko\\\Sample\\\PersonDataSource"
        method: getData
        args: [#id]
        lazyLoading: true
        supplySeveralFields: true
        onFail: checkException
        exceptionClass: \RuntimeException
        badReturnValue: emptyString
        fallbackSourceId: testFallbackSourceId
        depends: [#dependsFirst]
        preprocessor:
            class: ""
            method: somePreprocessor
            args: []
        processor:
            class: ""
            method: someProcessor
            args: []
```

Php format:
```php
[
    'object'    => [
        'dataSourcesStore'    => [
            [
                'id'=> 'personSource',
                'class'=> 'Kassko\Sample\PersonDataSource',
                'method'=> 'getData',
                'args' => ['#id'],
                'lazyLoading' => true,
                'supplySeveralFields' => true,
                'onFail'    => 'checkException',
                'exceptionClass' => '\RuntimeException',
                'badReturnValue' => 'emptyString',
                'fallbackSourceId' => 'testFallbackSourceId',
                'depends' => ['#dependsFirst'],
                'preprocessor' => [
                    'class' => '',
                    'method' => 'somePreprocessor',
                    'args' => []
                ],
                'processor' => [
                    'class' => '',
                    'method' => 'someProcessor',
                    'args' => []
                ]
            ]
        ]
    ]
];
```

To know more about the "Method config" usage, please see its dedicated documentation ["Method config"](#method-config).


### ExcludeImplicitSource config

Annotation format:
```php
use Kassko\DataMapper\Annotation as DM;

/**
 * @DM\RefImplicitSource(id="RefImplicitSourceId")
 */
class SomeClass
{
    protected $fieldToBindAutoToImplicitSource;

    protected $anotherFieldToBindAutoToImplicitSource;

    /**
     *@DM\ExcludeImplicitSource
     */
    protected $fieldNotToBindAutoToImplicitSource;
}
```

Yaml format:
```yml
object:
    RefImplicitSource: RefImplicitSourceId
fields:
    fieldToBindAutoToImplicitSource:
        name: fieldToBindAutoToImplicitSource
    anotherFieldToBindAutoToImplicitSource:
        name: anotherFieldToBindAutoToImplicitSource
    fieldNotToBindAutoToImplicitSource:
        name: fieldNotToBindAutoToImplicitSource
fieldsNotToBindToImplicitSource: [fieldNotToBindAutoToImplicitSource]
```

Php format:
```php
[
    'object' => [
        'RefImplicitSource' => 'RefImplicitSourceId'
    ],
    'fields' => [
        'fieldToBindAutoToImplicitSource' => [
            'name'      => 'fieldToBindAutoToImplicitSource',
        ],
        'anotherFieldToBindAutoToImplicitSource' => [
            'name'      => 'anotherFieldToBindAutoToImplicitSource',
        ],
        'fieldNotToBindAutoToImplicitSource' => [
            'name'      => 'fieldNotToBindAutoToImplicitSource',
        ]
    ],
    'fieldsNotToBindToImplicitSource' => [
        'fieldNotToBindAutoToImplicitSource'
    ]
];
```

### Field config

Annotation format:
```php
use Kassko\DataMapper\Annotation as DM;

class SomeClass
{
    /**
     * @DM\Field(
     *      name="FirstField",
     *      type="string",
     *      class="stdClass",
     *      readConverter="readConvertFirstField",
     *      writeConverter="writeConvertFirstField",
     *      fieldMappingExtensionClass="ExtensionClass"
     * )
     */
    protected $fieldOne;

    /**
     * @DM\Field(
     *      name="SecondField",
     *      type="integer",
     *      class="\DateTime",
     *      readDateConverter="readDateConvertSecondField",
     *      writeDateConverter="writeDateConvertSecondField",
     *      fieldMappingExtensionClass="ExtensionClass",
     *      defaultValue="12"
     * )
     */
    protected $fieldTwo;

    /**
     * @DM\Field(
     *      name="DateField",
     *      type="date"
     * )
     */
    protected $dateField;
}
```

Yaml format:
```yml
fields:
    fieldOne:
        name: FirstField
        type: string
        class: stdClass
        readConverter: readConvertFirstField
        writeConverter: writeConvertFirstField
        fieldMappingExtensionClass: ExtensionClass
    fieldTwo:
        name: SecondField
        type: integer
        class: "\\\DateTime"
        readDateConverter: readDateConvertSecondField
        writeDateConverter: writeDateConvertSecondField
        fieldMappingExtensionClass: ExtensionClass
        defaultValue: 12
    dateField:
        name: DateField
        type: date
```

Php format:
```php
[
    'fields' => [
        'fieldOne'  => [
            'name'                       => 'FirstField',
            'type'                       => 'string',
            'class'                      => 'stdClass',
            'readConverter'              => 'readConvertFirstField',
            'writeConverter'             => 'writeConvertFirstField',
            'fieldMappingExtensionClass' => 'ExtensionClass',
        ],
        'fieldTwo'  => [
            'name'                       => 'SecondField',
            'type'                       => 'integer',
            'class'                      => '\DateTime',
            'readDateConverter'          => 'readDateConvertSecondField',
            'writeDateConverter'         => 'writeDateConvertSecondField',
            'fieldMappingExtensionClass' => 'ExtensionClass',
            'defaultValue'               => 12
        ],
        'dateField' => [
            'name'                       => 'DateField',
            'type'                       => 'date'
        ]
    ]
];
```

### Getter config

Annotation format:
```php
use Kassko\DataMapper\Annotation as DM;

class SomeClass
{
    /**
     * @DM\Getter(
     *      name="getterName"
     * )
     */
    protected $firstField;

    /**
     * @DM\Getter(
     *      prefix="is"
     * )
     */
    protected $secondField;
}
```

Yaml format:
```yml
fields:
    firstField:
        name: firstField
        getter: 
            name: getterName
    secondField:
        name: secondField
        getter:
            prefix: is
```

Php format:
```php
[
    'fields'    => [
        'firstField'    => [
            'name'      => 'firstField',
            'getter'    => ['name' => 'getterName'],
        ],
        'secondField'    => [
            'name'      => 'secondField',
            'getter'    => ['prefix' => 'is'],
        ],
    ]
];
```

### Id config

Annotation format:
```php
use Kassko\DataMapper\Annotation as DM;

class SomeClass
{
    /**
     * @DM\Id
     */
    protected $firstField;
}
```

Yaml format:
```yml
id: firstField
fields: ["firstField"]
```

Php format:
```php
[
    'id'        => 'firstField',
    'fields'    => ['firstField']
];
```

### IdCompositePart config

Annotation format:
```php
use Kassko\DataMapper\Annotation as DM;

class SomeClass
{
    /**
     * @DM\IdCompositePart
     */
    protected $firstField;

    /**
     * @DM\IdCompositePart
     */
    protected $secondField;
}
```

Yaml format:
```yml
idComposite:    [firstField, secondField]
fields:         [firstField, secondField]
```

Php format:
```php
[
    'idComposite'   => ['firstField', 'secondField'],
    'fields'    => ['firstField', 'secondField']
];
```

### Listeners config

Annotation format:
```php
use Kassko\DataMapper\Annotation as DM;

/**
 * @DM\Listeners(
 *  preHydrate = @DM\Methods({
 *      @DM\Method(class="SomeClass", method="preHydrateMethodName")   
 * }),
 *  postHydrate = @DM\Methods({
 *      @DM\Method(class="SomeClass", method="postHydrateMethodName"),
 *      @DM\Method(class="SomeClassB", method="postHydrateMethodName") 
 * }),
 *  preExtract = @DM\Methods({
 *      @DM\Method(class="SomeClass", method="preExtractMethodName", args="foo")   
 * }),
 *  postExtract = @DM\Methods({
 *      @DM\Method(class="SomeClass", method="postExtractMethodName", args={"foo", "#bar"})   
 * })
 * )
 */
class SomeClass
{
}
```

Yaml format:
```yml
listeners:
    preHydrate: 
        - {class: SomeClass, method: preHydrateMethodName}
    postHydrate: 
        - {class: SomeClass, method: postHydrateMethodName}
        - {class: SomeClassB, method: postHydrateMethodName}
    preExtract: 
        - {class: SomeClass, method: preExtractMethodName, args: foo}
    postExtract: 
        - {class: SomeClass, method: postExtractMethodName, args: ['foo', '#bar']}
```

Php format:
```php
[
    'listeners' => [
        'preHydrate' => ['class' => 'SomeClass', 'method' => 'preHydrateMethodName'],                
        'postHydrate' => 
        [
            ['class' => 'SomeClass', 'method' => 'postHydrateMethodName'],
            ['class' => 'SomeClassB', 'method' => 'postHydrateMethodName'],
        ], 
        'preExtract' => ['class' => 'SomeClass', 'method' => 'preExtractMethodName', 'args' => 'foo'],
        'postExtract' => ['class' => 'SomeClass', 'method' => 'postExtractMethodName', 'args' => ['foo', '#bar']],
    ]
];
```

### Method config

Annotation format:
```php
use Kassko\DataMapper\Annotation as DM;

/**
 * @DM\Method(method="someMethod")
 * @DM\Method(class="SomeClass", method="someMethod", args="abc")
 * @DM\Method(class="@some_object_created_from_factory_or_container", method="someMethod", args= {"##this", "abc"}
 */
```

Yaml format:
```yml
some_key:
    'method': someMethod

some_key:
    class: SomeClass
    method: someMethod
    args: abc

some_key:
    class: @some_object_created_from_factory_or_container
    method: someMethod
    args: ['##this', 'abc']
```

Php format:
```php
[
    'method' => 'someMethod',
]

[
    'class' => 'SomeClass',
    'method' => 'someMethod',
    'args' => 'abc'
]

[
    'class' => '@some_object_created_from_factory_or_container',
    'method' => 'someMethod',
    'args' => ['##this', 'abc']
]
```

### Object config

Annotation format:
```php
use Kassko\DataMapper\Annotation as DM;

/**
 * @DM\Object(
 *      fieldExclusionPolicy="exclude_all",
 *      providerClass="testProviderClass",
 *      readDateConverter="testReadDateConverter",
 *      writeDateConverter="testWriteDateConverter",
 *      propertyAccessStrategy=true,
 *      fieldMappingExtensionClass="testFieldMappingExtensionClass",
 *      classMappingExtensionClass="testClassMappingExtensionClass"
 * )
 */
class SomeClass
{

}
```

Yaml format:
```yml
fieldExclusionPolicy: exclude_all
object:
    providerClass: testProviderClass
    readDateConverter: testReadDateConverter
    writeDateConverter: testWriteDateConverter
    propertyAccessStrategy: true
    fieldMappingExtensionClass: testFieldMappingExtensionClass
    classMappingExtensionClass: testClassMappingExtensionClass
```

Php format:
```php
[
    'fieldExclusionPolicy'  => 'exclude_all',
    'object'    => [
        'providerClass'         => 'testProviderClass',
        'readDateConverter'     => 'testReadDateConverter',
        'writeDateConverter'    => 'testWriteDateConverter',
        'propertyAccessStrategy'=> true,
        'fieldMappingExtensionClass' => 'testFieldMappingExtensionClass',
        'classMappingExtensionClass' => 'testClassMappingExtensionClass'
    ]
];
```

### ObjectListeners config - DEPRECATED - SEE Listeners Config

Annotation format:
```php
use Kassko\DataMapper\Annotation as DM;

/** 
 * @DM\ObjectListeners(
 *      classList={"SomeListenerAClass", "SomeListenerBClass"}
 * )
 */
class SomeClass
{
}
```

Yaml format:
```yml
objectListeners: [SomeListenerAClass, SomeListenerBClass]
```

Php format:
```php
[
    'objectListeners'   => ['SomeListenerAClass', 'SomeListenerBClass']
];
```

### PostExtract config - DEPRECATED - SEE Listeners Config

Deprecated. Use [Listeners config](#listeners-config) instead.

Annotation format:
```php
use Kassko\DataMapper\Annotation as DM;

/**
 * @DM\Object(classMappingExtensionClass="mappingExtensionClass")
 *
 * @DM\PostExtract(method="postExtractMethodName")
 */
class SomeClass
{
}
```

Yaml format:
```yml
object:
    classMappingExtensionClass: mappingExtensionClass

interceptors:
    postExtract: postExtractMethodName
```

Php format:
```php
[
    'object' => ['classMappingExtensionClass' => 'mappingExtensionClass'],
    'interceptors'  => [
        'postExtract'    => 'postExtractMethodName'
    ]
];
```

### PostHydrate config - DEPRECATED - SEE Listeners Config

Deprecated. Use [Listeners config](#listeners-config) instead.

Annotation format:
```php
use Kassko\DataMapper\Annotation as DM;

/**
 * @DM\Object(classMappingExtensionClass="mappingExtensionClass")
 *
 * @DM\PostHydrate(method="postHydrateMethodName")
 */
class SomeClass
{
}
```

Yaml format:
```yml
object:
    classMappingExtensionClass: mappingExtensionClass

interceptors:
    postHydrate: postHydrateMethodName
```

Php format:
```php
[
    'object' => ['classMappingExtensionClass' => 'mappingExtensionClass'],
    'interceptors'  => [
        'postHydrate'    => 'postHydrateMethodName'
    ]
];
```

### PreExtract config - DEPRECATED - SEE Listeners Config

Deprecated. Use [Listeners config](#listeners-config) instead.

Annotation format:
```php
use Kassko\DataMapper\Annotation as DM;

/**
 * @DM\Object(classMappingExtensionClass="mappingExtensionClass")
 *
 * @DM\PreExtract(
 *      method="preExtractMethodName"
 * )
 */
class SomeClass
{
}
```

Yaml format:
```yml
object:
    classMappingExtensionClass: mappingExtensionClass

interceptors:
    preExtract: preExtractMethodName
```

Php format:
```php
[
    'object' => ['classMappingExtensionClass' => 'mappingExtensionClass'],
    'interceptors'  => [
        'preExtract'    => 'preExtractMethodName'
    ]
];
```

### PreHydrate config - DEPRECATED - SEE Listeners Config

Deprecated. Use [Listeners config](#listeners-config) instead.

Annotation format:
```php
use Kassko\DataMapper\Annotation as DM;

/**
 * @DM\Object(classMappingExtensionClass="mappingExtensionClass")
 *
 * @DM\PreHydrate(method="preHydrateMethodName")
 */
class SomeClass
{
}
```

Yaml format:
```yml
object:
    classMappingExtensionClass: mappingExtensionClass

interceptors:
    preHydrate: preHydrateMethodName
```

Php format:
```php
[
    'object' => ['classMappingExtensionClass' => 'mappingExtensionClass'],
    'interceptors'  => [
        'preHydrate'    => 'preHydrateMethodName'
    ]
];
```

### Provider config - DEPRECATED - SEE DataSource Config

Deprecated. Use [DataSource config](#datasource-config) instead.
Configuration is the same as DataSource.

### ProvidersStore config - DEPRECATED - SEE DataSourcesStore Config

Deprecated. Use [DataSourcesStore config](#datasourcesstore-config) instead.
Configuration is the same as DataSourceStore.

### RefImplicitSource config

Annotation format:
```php
use Kassko\DataMapper\Annotation as DM;

/**
 * @DM\RefImplicitSource(id="RefImplicitSourceId")
 */
class SomeClass
{

}
```

Yaml format:
```yml
object:
    RefImplicitSource: RefImplicitSourceId
fields:
    mockField:
        name: mockFieldName
```

Php format:
```php
[
    'object' => [
        'RefImplicitSource' => 'RefImplicitSourceId'
    ],
    'fields' => [
        'mockField' => [
            'name'      => 'mockFieldName',
        ]
    ]
];
```

### RefSource config

Annotation format:
```php
use Kassko\DataMapper\Annotation as DM;

/**
 * @DM\DataSourcesStore({
 *      @DM\DataSource(
 *          id="someDataSource",
 *          class="Kassko\Sample\PersonDataSource",
 *          method="getData"
 *      )
 * })
 */
class SomeClass
{
    /**
     * @DM\RefSource(id="someDataSource")
     */
    private $fieldOne;
}
```

Yaml format:
```yml
object:
    dataSourcesStore:
        - id: personSource
        class: "Kassko\\\Sample\\\PersonDataSource"
        method: getData
fields:
    fieldOne:
        name: fieldOne
        refSource: someDataSource
```

Php format:
```php
[
    'object'    => [
        'dataSourcesStore'    => [
            [
                'id'=> 'personSource',
                'class'=> 'Kassko\Sample\PersonDataSource',
                'method'=> 'getData',
            ]
        ]
    ],
    'fields'    => [
        'fieldOne' => [
            'name'  => 'fieldOne',
            'refSource' => 'someDataSource',
        ]
    ],
];
### Setter config

Annotation format:
```php
use Kassko\DataMapper\Annotation as DM;

class SomeClass
{
    /**
     * @DM\Setter(
     *      prefix="setterPrefix",
     *      name="setterName"
     * )
     */
    protected $firstField;
}
```

Yaml format:
```yml
fields:
    firstField:
        name: firstField
        setter: 
            name: setterName
```

Php format:
```php
[
    'fields'    => [
        'firstField'    => [
            'name'      => 'firstField',
            'setter'    => ['name' => 'setterName'],
        ]
    ]
];
```

### ToExclude config

Annotation format:
```php
use Kassko\DataMapper\Annotation as DM;

/**
 * Optional because it's the default settings for fieldExclusionPolicy.
 * @DM\Object(fieldExclusionPolicy="include_all")
 */
class SomeClass
{
    /**
     * @DM\ToExclude
     */
    protected $excludedField;

    /**
     * @DM\Field
     */
    protected $anotherField;
}
```

Yaml format:
```yml
# Optional because it's the default settings for fieldExclusionPolicy.
# object:
#    fieldExclusionPolicy: include_all
excludedFields: [excludedField]
fields:
  excludedField:
    name: excludedField
  anotherField:
    name: anotherField
```

Php format:
```php
[
    /*
    //Optional because it's the default settings for fieldExclusionPolicy.
    'object' => [
        'fieldExclusionPolicy' => 'include_all'
    ],
    */
    'excludedFields' => [
        'excludedField'
    ],
    'fields'  => [
        'excludedField' => [
            'name'     => 'excludedField'
        ],
        'anotherField'         => [
            'name'     => 'anotherField'
        ]
    ]
];
```

### ToInclude config

Annotation format:
```php
use Kassko\DataMapper\Annotation as DM;

/**
 * @DM\Object(fieldExclusionPolicy="exclude_all")
 */
class SomeClass
{
    /**
     * @DM\Include
     */
    protected $includedField;

    /**
     * @DM\Field
     */
    protected $field;
}
```

Yaml format:
```yml
fieldExclusionPolicy: exclude_all
fieldsToInclude: [includedField]
fields:
    includedField:
        name: includedField
    anotherField:
        name: anotherField
```

Php format:
```php
[
    'fieldExclusionPolicy' => 'exclude_all'
    'fieldsToInclude' => [
        'includedField'
    ],
    'fields'  => [
        'includedField' => [
            'name'     => 'includedField'
        ],
        'anotherField' => [
            'name'     => 'anotherField'
        ],
    ]
];
```

### Transient config

Annotation format:
```php
use Kassko\DataMapper\Annotation as DM;

class SomeClass
{
    /**
     * @DM\Transient
     */
    protected $firstField;

    /**
     * @var string
     */
    protected $secondField;
}
```

Yaml format:
```yml
transient: [firstField]
fields: [firstField]
```

Php format:
```php
[
    'transient' => ['firstField'],
    'fields'    => [
        'firstField'    => [
            'name'      => 'firstFieldName'
        ]
    ]
];
```

### ValueObject config - DEPRECATED - SEE Config Config

Deprecated. Use [Config config](#config-config) instead.

Annotation format:
Annotation `@Kassko\DataMapper\Annotation\ValueObject` becomes `@Kassko\DataMapper\Annotation\Config`.

Yaml and Php format:
Now, there's a field key `'fields.some_field.config'`. Uses it instead of the global key `'valueObjects'`.

Annotation format:
```php
use Kassko\DataMapper\Annotation as DM;

class ValueObject
{
    /**
     * @DM\Config(
     *      class="\ValueObjectClass",
     *      mappingResourceName="valueObjectResourceName",
     *      mappingResourcePath="valueObjectResourcePath",
     *      mappingResourceType="valueObjectResourceType"
     * )
     */
    protected $firstField;
}
```

Yaml format:
```yml
fields:
  firstField:
    name: firstField
valueObjects:
    firstField:
        class: "\\\ValueObjectClass"
        mappingResourceName: valueObjectResourceName
        mappingResourcePath: valueObjectResourcePath
        mappingResourceType: valueObjectResourceType
```

Php format:
```php
[
    'fields' => [
        'firstField' => [
            'name'         => 'firstField',
        ]
    ],
    'valueObjects' => [
        'firstField'    => [
            'class' => '\ValueObjectClass',
            'mappingResourceName' => 'valueObjectResourceName',
            'mappingResourcePath' => 'valueObjectResourcePath',
            'mappingResourceType' => 'valueObjectResourceType'
        ]
    ]
];
```

### Version config

Annotation format:
```php
use Kassko\DataMapper\Annotation as DM;

class SomeClass
{
    /**
     * @DM\Version
     */
    protected $firstField;
}
```

Yaml format:
```yml
version: firstField
fields: [firstField]
```

Php format:
```php
[
    'version'   => 'firstField',
    'fields'    => ['firstField']
];
```

========================================

* [`Result builder in details`](https://github.com/kassko/data-mapper/blob/master/Resources/doc/result_builder.md)
* [`Map some property names to keys of your raw datas`](https://github.com/kassko/data-mapper/blob/master/Resources/doc/map_properties.md)
* [`Convert some values before hydration or before extraction`](https://github.com/kassko/data-mapper/blob/master/Resources/doc/converters.md)
* [`Use a data source for a property`](https://github.com/kassko/data-mapper/blob/master/Resources/doc/data_source.md)
* [`Lazy load some properties`](https://github.com/kassko/data-mapper/blob/master/Resources/doc/lazy_loading.md)
* [`Use a data source for a set of properties`](https://github.com/kassko/data-mapper/blob/master/Resources/doc/data_source.md)
* [`Hydrate nested object or nested collections`](https://github.com/kassko/data-mapper/blob/master/Resources/doc/nested_object_hydration.md)
* [`Use fallback data sources`](https://github.com/kassko/data-mapper/blob/master/Resources/doc/fallback_source.md)
* [`Use local config`](https://github.com/kassko/data-mapper/blob/master/Resources/doc/value_objects.md)
* [`Select the fields to map`](https://github.com/kassko/data-mapper/blob/master/Resources/doc/field_exclusion_policy.md)
* [`Getters, isser, hasser and more get methods`](https://github.com/kassko/data-mapper/blob/master/Resources/doc/getter_setter.md)
* [`Choose or change an object mapping configurations at runtime`](https://github.com/kassko/data-mapper/blob/master/Resources/doc/runtime_configuration.md)
* [`Choose your mapping configuration format`](https://github.com/kassko/data-mapper/blob/master/Resources/doc/mapping_format.md)
* [`Configuration reference documentation`](https://github.com/kassko/data-mapper/blob/master/Resources/doc/configuration.md)
* [`Inner Yaml mapping reference documentation`](https://github.com/kassko/data-mapper/blob/master/Resources/doc/inner_yaml_mapping.md).
* [`Yaml mapping reference documentation`](https://github.com/kassko/data-mapper/blob/master/Resources/doc/yaml_file_mapping.md).
* [`Inner php mapping reference documentation`](https://github.com/kassko/data-mapper/blob/master/Resources/doc/inner_php_mapping.md).
* [`Php mapping reference documentation`](https://github.com/kassko/data-mapper/blob/master/Resources/doc/php_file_mapping.md).



