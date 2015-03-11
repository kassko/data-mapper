data-mapper
==================

[![Build Status](https://secure.travis-ci.org/kassko/data-mapper.png?branch=master)](https://travis-ci.org/kassko/data-mapper)
[![Latest Stable Version](https://poser.pugx.org/kassko/data-mapper/v/stable.png)](https://packagist.org/packages/kassko/data-mapper)
[![Total Downloads](https://poser.pugx.org/kassko/data-mapper/downloads.png)](https://packagist.org/packages/kassko/data-mapper)
[![Latest Unstable Version](https://poser.pugx.org/kassko/data-mapper/v/unstable.png)](https://packagist.org/packages/kassko/data-mapper)

# Presentation #

data-mapper component represents some raw data like objects as all data-mapper but it particularly allows to create complex representations. Use it:

* for persistence ignorance
* to provide converters for properties or raw data before hydration
* to use a specific database or a specific DBMS to hydrate one or a particular set of properties of your object
* to work with relation using a database or a DBMS different of the object owner one
* to map composite objects
* to bind mapping rules to object property level rather than object class level
* to choose or change an object mapping configurations at runtime
* to choose your mapping configuration format
* to work with various formats in the same application

# Installation #

Add to your composer.json:
```json
"require": {
    "kassko/data-mapper": "~0.11.0@alpha"
}
```

Note that:
* the second version number is used when compatibility is broken
* the third for new feature
* the fourth for hotfix
* the first for new API or to go from pre-release to release (from 0 to 1)

## Usage ##

### Map your object ###

You create a mapping configuration (with annotations, yaml or php):

#### Annotations format ####
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
     * @DM\Field(name="COLOR")
     */
    private $color;

    public function getBrand() { return $this->brand; }
    public function setBrand($brand) { $this->brand = $brand; }
    public function getColor() { return $this->color; }
    public function setColor($color) { $this->color = $color; }
}
```

#### Yaml file format ####
```yaml
fields:
    brand: ~ # this field hasn't got specific configuration but we want the mapper to manage it (it will be hydrated and extracted)
    color:
        name: "COLOR"
```

#### Php file format ####
```php
return [
    'fields' => [
        'brand', //this field hasn't got specific configuration but we want the mapper to manage it (it will be hydrated and extracted)
        'color' => ['name' => 'COLOR'],
];
```

Later you'll see that you can use inner yaml or inner php mapping format and provide your own format.

### Use the hydrator ###

To do hydration and extraction operations, you get a DataMapper instance and you hydrate your object with its hydrator:
```php
$data = [
    'brand' => 'some brand',
    'COLOR' => 'blue',
];

$dataMapper = (new Kassko\DataMapper\DataMapperFactory)->instance();

$object = new Kassko\Sample\Watch;
$dataMapper->hydrator('Kassko\Sample\Watch')->hydrate($data, $object);
var_dump($object);
```

The code above will display:
```php
object(Watch)#283 (8) {
    ["brand":"Watch":private]=> string(10) "some brand"
    ["color":"Watch":private]=> string(4) "blue"
}
```

Inversely, you can extract values from your object to have raw result:
```php
$dataMapper = (new Kassko\DataMapper\DataMapperFactory)->instance();

$object = (new Kassko\Sample\Watch)->setBrand('some brand')->setColor('color');
$rawResult = $dataMapper->hydrator('Kassko\Sample\Watch')->extract($object);
```

### Use the ResultBuilder ###

If you have several records, you need to hydrate a collection rather than a single object. To do that, you can use the ResultBuilder which allows you to choose in what your result should be contained:
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

$dataMapper = (new Kassko\DataMapper\DataMapperFactory)->instance();

$dataMapper->resultBuilder('Kassko\Sample\Watch', $data)->all();//The result will be an array with two objects.
$dataMapper->resultBuilder('Kassko\Sample\Watch', $data)->first();//The result will be a watch object representing the first record.
```

Inversely, you can extract values of an object or a an object collection to have raw result:
```php
$dataMapper = (new Kassko\DataMapper\DataMapperFactory)->instance();

$dataMapper->resultBuilder('Kassko\Sample\Watch')->raw($object);
$dataMapper->resultBuilder('Kassko\Sample\Watch')->raw($collection);
```

There are other ways to get results. You can find below all the ways to get results with the ResultBuilder:

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

### Configure the DataMapper ###

The DataMapper has several settings that we haven't used.

For example, if you want to use another mapping format than the default one ('annotation'), you need to configure the DataMapper before its instanciation:

```php
use Kassko\DataMapper\DataMapperFactory;

$dataMapper = (new DataMapperFactory)
    ->settings(
        [
            'default_resource_type' => 'yaml_file',
            'default_resource_dir' => 'c:\mapping',
            'mapping.objects' =>
            [
                [
                    'class' => 'Kassko\Sample\Watch'
                    'resource_path' => 'c:\some_project\mapping\watch.yml'
                ],
                [
                    'class' => 'Kassko\Sample\Keyboard'
                    'resource_type' => 'annotations'
                ],
            ]
        ]
    )
    ->instance()
;
```

The code above means:
* the default mapping format is yaml_file
* by default, mapping yaml files are located in 'c:\mapping'
* except for the Watch class which overrides the yaml mapping file location to 'c:\some_project\mapping\watch.yml'
* the Keyboard class uses the annotations format

For more information, see the section named configuration reference.

### Ignore fields for mapping ###

Sometimes, you can have a field which has no correspondance with raw data and you want to set its value yourself. You can specify the DataMapper not to try to map this field.

#### Annotations format ####
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
     * @DM\Field(name="COLOR")
     */
    private $color;

    private $loadingDate;//This field has no field annotation, it will not be managed.

    public function getBrand() { return $this->brand; }
    public function setBrand($brand) { $this->brand = $brand; }
    public function getColor() { return $this->color; }
    public function setColor($color) { $this->color = $color; }
    public function setLoadingDate(\DateTime $loadingDate) { $this->loadingDate = $loadingDate; }
}
```

#### Yaml file format ####
```yaml
fields:
    brand: ~
    color:
        name: "COLOR"
# The field loadingDate do not appear in the fields section, it will not be managed.
```

#### Php file format ####
```php
return [
    'fields' => [
        'brand',
        'color' => ['name' => 'COLOR'],
];

//The field loadingDate do not appear in the fields section, it will not be managed.
```

But be careful, this feature has not been implemented to allow you to ignore some dependencies during mapping process. It's not a good practice to create persistent object with some dependencies (like a logger or a mailer). Your object shoud keep a POPO (Plain Old Php Object).

### Isser, haser and custom getters/setters ###

DataMapper automatically recognize getter (or isser or haser) and setter of a field.

#### Annotations format ####
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

#### Annotations format ####
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
     * @DM\Field
     * @DM\Getter(prefix="has")
     */
    private $alarm;

    /**
     * @DM\Field
     * @DM\Getter(prefix="are")
     */
    private $handsYellow;

    /**
     * @DM\Field(name="hasStopWatch")
     */
    private $stopWatch;

    /**
     * @DM\Field
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

### Uses converters ###

#### Annotations format ####
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

$dataMapper = (new Kassko\DataMapper\DataMapperFactory)->instance();
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

### Improve persistence ignorance ###

DataMapper implements the DataMapper pattern which requires to separate domain objects and the persistence logic. But above you have used converters and put their callbacks in the domain object. You also append use statement which imports classes related to persistence. So your object is not really agnostic of persistence.

You can put your converter' callbacks in a dedicated class:
```php
namespace Kassko\Sample;

use Kassko\DataMapper\Hydrator\HydrationContextInterface;
use Kassko\DataMapper\Hydrator\Value;

class WatchMappingExtension
{
    private static $brandCodeToLabelMap = [1 => 'Brand A', 2 => 'Brand B'];
    private static $brandLabelToCodeMap = ['Brand A' => 1, 'Brand B' => 2];

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

#### Annotations format ####
```php
namespace Kassko\Sample;

use Kassko\DataMapper\Annotation as DM;
use \DateTime;

/**
 * @DM\Object(classMappingExtensionClass="Kassko\\Sample\\WatchMappingExtension")
 */
class Watch
{
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
}
```

As you can see, you have removed all the code about the conversion and specified in your mapping the extension class which now contains this code.

You also can choose outer mapping formats ("yaml_file", "php_file") instead of inner ones ("annotations"). Annotations are not part of the executed code but they are still physically present in the domain object.

Your mapping in a Yaml file:
```yml
object:
    classMappingExtensionClass: "Kassko\\Sample\\WatchMappingExtension"
fields:
    brand:
        readConverter: readBrand
        writeConverter: writeBrand
    waterProof:
        readConverter: hydrateBool
        writeConverter: extractBool
    stopWatch:
        readConverter: hydrateBoolFromSymbol
        writeConverter: extractBoolToSymbol
    createdDate:
        name: created_date
        type: date
        readDateConverter: "Y-m-d H:i:s"
        writeDateConverter: "Y-m-d H:i:s"
```

And your domain object:
```php
namespace Kassko\Sample;

use \DateTime;

class Watch
{
    private $brand;
    private $waterProof;
    private $stopWatch;
    private $createdDate;

    public function getBrand() { return $this->brand; }
    public function setBrand($brand) { $this->brand = $brand; }
    public function isWaterProof() { return $this->waterProof; }
    public function setWaterProof($waterProof) { $this->waterProof = $waterProof; }
    public function hasStopWatch() { return $this->stopWatch; }
    public function setStopWatch($stopWatch) { $this->stopWatch = $stopWatch; }
    public function getCreatedDate() { return $this->createdDate; }
    public function setCreatedDate(DateTime $createdDate) { $this->createdDate = $createdDate; }
}
```

Is the Watch object persisted ? We really don't know ! However, you could reply that's very practical to work with annotations because we can see both the domain object and its mapping rules. That's right ! Both have advantages and drawbacks. The choice is yours !

### Provider for toOne association ###

```php
namespace Kassko\Sample;

use Kassko\DataMapper\Annotation as DM;

class Keyboard
{
    /**
     * @DM\Field
     * @DM\Id
     */
    private $id;

    /**
     * @DM\Field
     */
    private $color;

    /**
     * @DM\ToOneProvider(objectClass="Kassko\Sample\Manufacturer", findMethod="find")
     * @DM\Field(name="manufacturer_id")
     */
    private $manufacturer;

    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id; }
    public function getColor() { return $this->color; }
    public function setColor($color) { $this->color = $color; }
    public function getManufacturer() { return $this->manufacturer; }
    public function setManufacturer(Manufacturer $manufacturer)
    {
        $this->manufacturer = $manufacturer;
    }
}
```

As you can guess, the "find" method is that of the manager of the object "Manufacturer" meanning "ManufacturerManager::find()".

```php
namespace Kassko\Sample;

/**
 * @DM\Object(providerClass="Kassko\Sample\ManufacturerManager")
 */
class Manufacturer
{
    /**
     * @DM\Field
     * @DM\Id
     */
    private $id;

    /**
     * @DM\Field
     */
    private $name;

    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id; }
    public function getName() { return $this->name; }
    public function setName($name) { $this->name = $name; }
}
```

```php
namespace Kassko\Sample;

class ManufacturerManager
{
    /**
     * Return a manufacturer object from its identity
     * @param integer $id The identity of object to find
     *
     * @return Manufacturer
     */
    public function find($id)
    {
        //Some stuff to find the manufacturer.
    }
}
```

The following code:
```php
$data = [
    'id' => 1,
    'color' => 'blue',
    'manufacturer_id' => 1
];

$dataMapper = (new Kassko\DataMapper\DataMapperFactory)->instance();
var_dump($dataMapper->resultBuilder('Kassko\Sample\Keyboard', $data)->single());
```

Will display:
```php
object(Keyboard)#283 (8) {
    ["id":"Keyboard":private]=> int(1)
    ["color":"Keyboard":private]=> string(4) "blue"
    ["manufacturer":"Manufacturer":private]=>
    object(Manufacturer)#320 (3) {
        ["id":"Manufacturer":private]=> int(1)
        ["name":"Manufacturer":private]=> string(10) "Some brand"
    }
}
```

Note that in an object, if no property is specified as the identifier, the default identifier is a property named "$id". And if no property "$id" exists, an exception is thrown when the system attempt to know the object identifier.

Note also that for performance reasons, we can load the association "$manufacturer" only when we use it. For more details see the "Lazy loading" section.

### Provider for toMany relation ###

An relation "to many" is used similarly to "to one".

```php
namespace Kassko\Sample;

use Kassko\DataMapper\Annotation as DM;

class Keyboard
{
    /**
     * @DM\Field
     * @DM\Id
     */
    private $id;

    /**
     * @DM\Field
     */
    private $color;

    /**
     * @DM\Field
     * @DM\ToManyProvider(objectClass="Kassko\Sample\Shop", findMethod="findByKeyboard")
     */
    private $shops;

    public function __construct() { $this->shops = []; }

    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id; }
    public function getColor() { return $this->color; }
    public function setColor($color) { $this->color = $color; }
    public function getShops() { return $this->shops; }
    public function addShop(Shop $shop) { $this->shops[] = $shops; }
}
```

```php
namespace Kassko\Sample;

class ShopManager
{
    /**
     * Return shops witch sale a keyboard from a given identity
     * @param integer $id Id of the keyboard
     *
     * @return Shops[]
     */
    public function findByKeyboard($id)
    {
        //Some stuff to find shops witch sale the keyboard with identity "$id".
    }
}
```

```php
namespace Kassko\Sample;

/**
 * @DM\Object(providerClass="Kassko\Sample\ShopManager")
 */
class Shop
{
    /**
     * @DM\Id
     * @DM\Field
     */
    private $id;

    /**
     * @DM\Field
     */
    private $name;

    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id; }
    public function getName() { return $this->name; }
    public function setName($name) { $this->name = $name; }
}
```

The association name is the object class name not full qualified. So if "Shop" is the object class name, then "Shop" is the association name and then in "Keyboard" object the "adder" is "addShop()".

If the "Shop" FQCN (full qualified class name) was "Kassko\Sample\Shop", the association name would had been "Shop" and the adder would had been "addShop()".

But you can override this association name:
```php
namespace Kassko\Sample;

use Kassko\DataMapper\Annotation as DM;

class Keyboard
{
    /**
     * @DM\ToManyProvider(name="insertShop", objectClass="Kassko\Sample\Shop", findMethod="find")
     * @DM\Field
     */
    private $shops;

    public function __construct() { $this->shops = []; }

    public function insertShop(Shop $shop) { $this->shops[] = $shops; }
}
```

Usage:
```php
$data = [
    'id' => 1,
    'color' => 'blue'
];

//Here some stuff to create $dataMapper

var_dump($dataMapper->resultBuilder($data, 'Kassko\Sample\Keyboard')->single());
```

Possible display result:
```php
object(Keyboard)#283 (8) {
    ["id":"Keyboard":private]=> int(1)
    ["color":"Keyboard":private]=> string(4) "blue"
    array(3) {
        [0] =>
        ["shops":"Shop":private]=>
            object(Shop)#320 (3) {
                ["id":"Shop":private]=> int(1)
                ["name":"Shop":private]=> string(14) "Shop A"
            }
        }
        [1] =>
        ["shops":"Shop":private]=>
            object(Shop)#320 (3) {
                ["id":"Shop":private]=> int(2)
                ["name":"Shop":private]=> string(14) "Shop B"
            }
        }
    }
}
```

Note that for performance reasons, we can load the association "$shop" only when we use it. For more details see the "Lazy loading" section.

### Use a specific database or a specific DBMS to hydrate one or a particular set of properties of your object ###

data-mapper can use a specific database or a specific DBMS to hydrate one or a particular set of properties of your object. You can achieve that by creating a specific data source and requiring it in your mapping.

```php
namespace Kassko\Sample;

use Kassko\DataMapper\Annotation as DM;

class Information
{
    /**
     * @DM\DataSource(class="Kassko\Sample\KeyboardManager", method="loadKeyboards")
     * @DM\Field
     */
    private $keyboards = [];

    /**
     * @DM\DataSource(class="Kassko\Sample\ShopManager", method="loadBestShop")
     * @DM\Field
     */
    private $bestShop;

    public function setBestShop(Shop $shop) { $this->bestShop = $bestShop; }
    public function addShop(Keyboard $keyboard) { $this->keyboard[] = $keyboard; }
    public function getKeyboards() { return $this->keyboards; }
    public function getBestShop() { return $this->bestShop; }
}
```

```php
namespace Kassko\Sample;

use Kassko\DataMapper\Annotation as DM;

class Keyboard
{
    /**
     * @DM\Field
     */
    private $brand;

    /**
     * @DM\Field
     */
    private $color;
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

class ShopManager
{
    /**
     * A connection to Shop database on MySql.
     */
    private $shopConnection;

    public function loadBestShop(Information $info)
    {
        $rawData = $shopConnection->execute('some query to retrieve the best shop raw data');

        $info->setBestShop((new Shop())->setName($rawData['name'])->setAddress($rawData['address']));
    }
}
```

```php
namespace Kassko\Sample;

class KeyboardManager
{
    /**
     * A connection to Instrument database on SqlServer.
     */
    private $instrumentConnection;

    public function loadKeyboards(Information $info)
    {
        $rawData = $instrumentConnection->execute('SELECT brand, color FROM keyboard');

        foreach ($rawData as $record) {
            $info->addKeyboard((new Keyboard())->setBrand($record['brand'])->setColor($record['color']));
        }
    }
}
```
We can load the properties "bestShop" and "keyboard" only when we use it. For more details see the "Lazy loading" section.

### Create an object composite ###

A custom data source is also usefull to create an object composite. That is to say an object which contains other objects or some collections but there is no relation with these objects (unlike object in relation toOneProvider or toManyProvider). In the previous section, the object Information is an object composite.

### RelationProvider using a database or a DBMS different of its object owner one ###

If you need it, you can override the provider class for toOneProvider or toManyProvider. It allows you to have relations between different databases or between different DBMS.

Below, the toOneProvider is ManufacturerManager::find().

```php
namespace Kassko\Sample;

use Kassko\DataMapper\Annotation as DM;

class Keyboard
{
    /**
     * @DM\ToOneProvider(objectClass="Kassko\Sample\Manufacturer", findMethod="find")
     * @DM\Field
     */
    private $manufacturer;
}
```

```php
namespace Kassko\Sample;

use Kassko\DataMapper\Annotation as DM;

/**
 * @DM\Object(providerClass="Kassko\Sample\ManufacturerManager")
 */
class Manufacturer
{
    /**
     * @DM\Field
     * @DM\Id
     */
    private $id;

    /**
     * @DM\Field
     */
    private $name;

    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id; }
    public function getName() { return $this->name; }
    public function setName($name) { $this->name = $name; }
}
```

With the following keyboard class:
```php
namespace Kassko\Sample;

use Kassko\DataMapper\Annotation as DM;

class Keyboard
{
    /**
     * @DM\ToOneProvider(objectClass="Kassko\Sample\Manufacturer", class="UnconventionnalManager" findMethod="find")
     * @DM\Field
     */
    private $manufacturer;
}
```

The find method become "UnconventionnalManager::find()".

### Lazy load toOneProvider, toManyProvider and Provider ###

You can lazy load associations ToOneProvider:

```php
namespace Kassko\Sample;

use Kassko\DataMapper\Annotation as DM;
use Kassko\DataMapper\ObjectExtension\LazyLoadableTrait;

class Keyboard
{
    use LazyLoadableTrait;

    /**
     * @DM\Field
     * @DM\Id
     */
    private $id;

    /**
     * @DM\Field
     */
    private $color;

    /**
     * @DM\ToOneProvider(objectClass="Kassko\Sample\Manufacturer", findMethod="find", lazyLoading="true")
     * @DM\Field(name="manufacturer_id")
     */
    private $manufacturer;

    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id; }
    public function getColor() { return $this->color; }
    public function setColor($color) { $this->color = $color; }

    public function getManufacturer()
    {
        $this->loadProperty('manufacturer');//<= Load the manufacturer from the property name if not loaded.
        return $this->manufacturer;
    }

    public function setManufacturer(Manufacturer $manufacturer)
    {
        $this->manufacturer = $manufacturer;
    }
}
```

Or ToManyProvider:

```php
namespace Kassko\Sample;

use Kassko\DataMapper\Annotation as DM;
use Kassko\DataMapper\ObjectExtension\LazyLoadableTrait;

class Keyboard
{
    /**
     * @DM\Field
     * @DM\Id
     */
    private $id;

    /**
     * @DM\Field
     */
    private $color;

    /**
     * @DM\Field
     * @DM\ToManyProvider(objectClass="Kassko\Sample\Shop", findMethod="findByKeyboard", lazyLoading="true")
     */
    private $shops;

    public function __construct() { $this->shops = []; }

    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id; }
    public function getColor() { return $this->color; }
    public function setColor($color) { $this->color = $color; }

    public function getShops()
    {
        $this->loadProperty('shops');//<= Load shops from the property name if not loaded.
        return $this->shops;
    }

    public function addShop(Shop $shop) { $this->shops[] = $shops; }
}
```

Or DataSource:
```php
namespace Kassko\Sample;

use Kassko\DataMapper\Annotation as DM;
use Kassko\DataMapper\ObjectExtension\LazyLoadableTrait;

class Information
{
    /**
     * @DM\DataSource(class="Kassko\Sample\KeyboardManager", method="loadKeyboards", lazyLoading="true")
     * @DM\Field
     */
    private $keyboards = [];

    /**
     * @DM\DataSource(class="Kassko\Sample\ShopManager", method="loadBestShop", lazyLoading="true")
     * @DM\Field
     */
    private $bestShop;

    public function setBestShop(Shop $shop) { $this->bestShop = $bestShop; }
    public function addShop(Keyboard $keyboard) { $this->keyboard[] = $keyboard; }
    public function getKeyboards()
    {
        $this->loadProperty('keyboards');//<= Load keyboards from the property name if not loaded.
        return $this->keyboards;
    }

    public function getBestShop()
    {
        $this->loadProperty('bestShop');//<= Load the best shop from the property name if not loaded.
        return $this->bestShop;
    }
}
```

### Provide a custon hydrator ###

### Use several mapping configuration for the same domain object and switch mapping configuration on runtime for a domain object ###

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

### Map composite objects ###

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
     * @DM\Field
     * @DM\ValueObject(class="Kassko\Sample\Address", mappingResourceType="yaml", mappingResourceName="billing_address.yml")
     */
    private $billingAddress;//$billingAddress is a value object.

    /**
     * @DM\Field
     * @DM\ValueObject(class="Kassko\Sample\Address", mappingResourceType="yaml", mappingResourceName="shipping_address.yml")
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

### Assemble DTO (Data Transfer Objects) ###

### Include mapping configuration in other ones or inherit some mapping configurations ###

### Field mapping level and class mapping level and inheritance ###
If all fields use a same option, you can configure this option at "object" level.

### Class mapping inheritance ###
This section will be written later.

### Resource mapping inheritance ###
This section will be written later.

### Use inner configuration ###
This section will be written later.

### Provide a custom configuration ###
This section will be written later.

### Work with various mapping configuration formats in the same application ###
This section will be written later.

### Cache your object ###
This section will be written later.

[see more in cache reference documentation](https://github.com/kassko/data-mapper/blob/master/Resources/doc/cache.md).

### You can attach listeners to an action ###
This section will be written later.

[see more in listener reference documentation](https://github.com/kassko/data-mapper/blob/master/Resources/doc/listener.md).

### You can use public properties instead of getters/setters ###
This section will be written later.

[see more in public properties reference documentation](https://github.com/kassko/data-mapper/blob/master/Resources/doc/public_property.md).

### You can log in your object without injecting to it a logger dependency ###
This section will be written later.

[see more in log reference documentation](https://github.com/kassko/data-mapper/blob/master/Resources/doc/log.md).

### Use a service without injecting a dependency in your domain object ###
This section will be written later.

### API details ###

#### Create an adapter for the cache ####
The default cache implementation used is Kassko\DataMapper\Cache\ArrayCache. You can provide an other implementation (for example in settings ['cache' => ['metadata' => [instance => $someCacheInstance]]]) but it must be compatible with Kassko\Cache\CacheInterface. You can enforce this compatibility if you provide an adapter which wrap your cache implementation. Here is an example of adapter:

```php
use Kassko\DataMapper\Cache\CacheInterface as KasskoCacheInterface;
use Doctrine\Common\Cache\Cache as DoctrineCacheInterface;

/**
 * A cache adapter to use the Kassko cache interface with a Doctrine cache implementation.
 */
class DoctrineCacheAdapter implements KasskoCacheInterface
{
    private $doctrineCache;

    public function __construct(DoctrineCacheInterface $doctrineCache)
    {
        $this->doctrineCache = $doctrineCache;
    }

    public function fetch($id)
    {
        return $this->doctrineCache->fetch($id);
    }

    /**
     * {@inheritdoc}
     */
    public function contains($id)
    {
        return $this->doctrineCache->contains($id);
    }

    /**
     * {@inheritdoc}
     */
    public function save($id, $data, $lifeTime = 0)
    {
        return $this->doctrineCache->save($id, $data, $lifeTime);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        return $this->doctrineCache->delete($id);
    }
}
```

At the present time, there is no standard cache interface like the PSR-3 PSR\Logger\LoggerInterface.
PSR-6 should provide one ? That's why the data-mapper has it's own cache interface and you should provide an adapter for it.


#### Create a ClassResolver instance ####
To know more about ClassResolver, see [the class-resolver documentation](https://github.com/kassko/class-resolver/blob/master/README.md)

#### Create an ObjectListenerResolver instance ####
This section will be written later.

### Create an adapter for the cache ###

### Configuration reference ###

### Mapping reference ###


### Configuration reference ###
----------

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


For more details about mapping you can read the mapping reference documentations:

#### Inner yaml format ####
[see inner Yaml mapping reference documentation](https://github.com/kassko/data-mapper/blob/master/Resources/doc/inner_yaml_mapping.md).

#### Yaml file format ####
[see yaml mapping reference documentation](https://github.com/kassko/data-mapper/blob/master/Resources/doc/yaml_file_mapping.md).

#### Inner php ####
[see inner php mapping reference documentation](https://github.com/kassko/data-mapper/blob/master/Resources/doc/inner_php_mapping.md).

#### Php file format ####
[see Php mapping reference documentation](https://github.com/kassko/data-mapper/blob/master/Resources/doc/php_file_mapping.md).



