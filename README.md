data-access
==================

[![Latest Stable Version](https://poser.pugx.org/kassko/data-access/v/stable.png)](https://packagist.org/packages/kassko/data-access)
[![Total Downloads](https://poser.pugx.org/kassko/data-access/downloads.png)](https://packagist.org/packages/kassko/data-access)
[![Latest Unstable Version](https://poser.pugx.org/kassko/data-access/v/unstable.png)](https://packagist.org/packages/kassko/data-access)

## Presentation ##

data-access component is a mapper which gives a lot of flexibility to representate some raw data like objects. Use it if you need:

* to keep your objects agnostic and preserve their base class (data-access implements Data Mapper pattern and not Active Record)
* to transform raw data before hydrating an object
* to create representations with nested objects and several nesting levels (Person => Address => Street)
* to map value objects (Address, Street) and reuse them with other mapping rules
* to reuse and inherit the mapping configurations
* to choose your mapping configuration format or to use various formats in the same application


## Installation ##

Add to your composer.json:
```json
"require": {
    "kassko/data-access": "~0.5.0@alpha"
}
```

## Utilisation ##

### Mapping configuration ###

You create a mapping configuration (with annotations, yaml or php):

#### Annotations format ####
```php

use Kassko\DataAccess\Annotation as DA;
use Kassko\DataAccess\Hydrator\HydrationContextInterface;
use Kassko\DataAccess\Hydrator\Value;
use \DateTime;

/**
 * @DA\PostHydrate(method="onAfterHydrate")
 * @DA\PostExtract(method="onAfterExtract")
 */
class Watch
{
    private static $brandCodeToLabelMap = [1 => 'Brand A', 2 => 'Brand B'];
    private static $brandLabelToCodeMap = ['Brand A' => 1, 'Brand B' => 2];

    /**
     * @DA\Field(readStrategy="readBrand", writeStrategy="writeBrand")
     */
    private $brand;

    /**
     * @DA\Field
     */
    private $color;

    /**
     * @DA\Field(name="created_date", type="date", readDateFormat="Y-m-d H:i:s", writeDateFormat="Y-m-d H:i:s")
     */
    private $createdDate;

    private $sealDate;

    /**
     * @DA\Field(readStrategy="hydrateBool", writeStrategy="extractBool")
     */
    private $waterProof;

    /**
     * @DA\Field(readStrategy="hydrateBoolFromSymbol", writeStrategy="extractBoolToSymbol", mappingExtensionClass="WatchCallbacks")
     */
    private $stopWatch;

    /**
     * @DA\Field(readStrategy="hydrateBool", writeStrategy="extractBool")
     * @DA\Getter(name="canBeCustomized")
     */
    private $customizable;//Naturally, we also can customize setters with "Setter" annotation.

    private $noSealDate = false;

    public function getBrand() { return $this->brand; }

    public function setBrand($brand) { $this->brand = $brand; }

    public function getColor() { return $this->color; }

    public function setColor($color) { $this->color = $color; }

    public function getCreatedDate() { return $this->createdDate; }

    public function setCreatedDate(DateTime $createdDate) { $this->createdDate = $createdDate; }

    public function isWaterProof() { return $this->waterProof; }

    public function setWaterProof($waterProof) { $this->waterProof = $waterProof; }

    public function hasStopWatch() { return $this->stopWatch; }

    public function setStopWatch($stopWatch) { $this->stopWatch = $stopWatch; }

    public function canBeCustomized() { return $this->customizable; }

    public function setCustomizable($customizable) { $this->customizable = $customizable; }

    public function getSealDate() { return $this->sealDate; }

    public function setSealDate(DateTime $sealDate) { $this->sealDate = $sealDate; }

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

    public function onAfterHydrate(HydrationContextInterface $context)
    {
        if ('' === $context->getItem('seal_date')) {
            $value = $context->getItem('created_date');
            $this->noSealDate = true;
        } else {
            $value = $context->getItem('seal_date');
        }

        $this->sealDate = DateTime::createFromFormat('Y-m-d H:i:s', $value);
    }

    public function onAfterExtract(HydrationContextInterface $context)
    {
        if ($this->noSealDate) {
            $context->setItem('seal_date', '');
        } else {
            $context->setItem('seal_date', $this->sealDate->format('Y-m-d H:i:s'));
        }
    }
}
```

```php
class WatchCallbacks
{
    public static function hydrateBoolFromSymbol(Value $value)
    {
        $value->value = $value->value == 'X';
    }

    public static function extractBoolToSymbol(Value $value)
    {
        $value->value = $value->value ? 'X' : ' ';
    }
}
```

#### Yaml format ####
[see Yaml mapping reference documentation](https://github.com/kassko/data-access/blob/master/Resources/doc/yaml_mapping.md).

#### Yaml file format ####
[see Yaml file mapping reference documentation](https://github.com/kassko/data-access/blob/master/Resources/doc/yaml_file_mapping.md).

#### Php format ####
[see Php mapping reference documentation](https://github.com/kassko/data-access/blob/master/Resources/doc/php_mapping.md).

#### Php file format ####
[see Php file mapping reference documentation](https://github.com/kassko/data-access/blob/master/Resources/doc/php_file_mapping.md).

As you can see,
* A property without "Field" annotation is not managed (not hydrated and not extracted).
* You can transform raw data before hydration or object values before extraction.
* You can isolate transformation methods in a separated file (see the WatchCallbacks class) to keep your entity agnostic of the transformations.
* You can convert a date before hydrating or extracting it.
* Isser (see isWaterProof()) and has methods (see hasStopWatch()) are managed.
* But you can specify custom getter/setter (see canBeCustomized()).

### API Usage ###

To do hydration and extraction operations, you get a ResultBuilderFactory instance and you hydrate your object:
```php
$data = [
    'brand' => 'some brand',
    'color' => 'blue',
    'created_date' => '2014 09 14 12:36:52',
    'waterProof' => '1',
    'stopWatch' => 'X',
    'customizable' => '0',
    'seal_date' => '',
];

$resultBuilder = $resultBuilderFactory->create('Watch', $data);
$result = $resultBuilder->getResult();
var_dump($result);
```

The code above will display:
```php
object(Watch)#283 (8) {
    ["brand":"Watch":private]=> string(10) "some brand"
    ["color":"Watch":private]=> string(4) "blue"
    ["createdDate":"Watch":private]=>
        object(DateTime)#320 (3) { ["date"]=> string(19) "2014-09-14 12:36:52" ["timezone_type"]=> int(3) ["timezone"]=> string(13) "Europe/Berlin" }
    ["sealDate":"Watch":private]=>
        object(DateTime)#319 (3) { ["date"]=> string(19) "2014-09-14 12:36:52" ["timezone_type"]=> int(3) ["timezone"]=> string(13) "Europe/Berlin" }
    ["waterProof":"Watch":private]=> bool(true) ["stopWatch":"Watch":private]=> bool(true)
    ["customizable":"Watch":private]=> bool(false) ["noSealDate":"Watch":private]=> bool(true)
}
```

Inversely, you can extract values from your object to have raw result:
```php
$resultBuilder = $resultBuilderFactory->create('Watch');
$result = $resultBuilder->getRawResult($object);
var_dump($result);
```

As you can see, the getResult() method return the object in an array. Maybe you would prefer to get the object instead of an array containing this object. Note that there are several ways to get results:

### Ways to get results ###

```php
    /*
    Return an array of objects.
    So return an array with only one object, if only one fullfill the request.
    */
    $resultBuilder->getResult();
```

```php
    /*
    Return one object or null if no result found.

    If severals results are found, throw an exception
    Kassko\DataAccess\Result\Exception\NonUniqueResultException.
    */
    $resultBuilder->getOneOrNullResult();
```

```php
    /*
    Return the object found or a default result (like a Watch instance).

    If more than one result are found, throw an exception
    Kassko\DataAccess\Result\Exception\NonUniqueResultException.
    */
    $resultBuilder->getOneOrDefaultResult(new Watch);
```

```php
    /*
    Return the object found.

    If more than one result are found, throw an exception
    Kassko\DataAccess\Result\Exception\NonUniqueResultException.

    If no result found, throw an exception
    Kassko\DataAccess\Result\Exception\NoResultException.
    */
    $resultBuilder->getSingleResult();
```

```php
    Return the first object found or null if no result found.
    $resultBuilder->getFirstOrNullResult();
```

```php
    /*
    Return the first object found or a default result (like a Watch instance).

    If no result found, throw an exception
    Kassko\DataAccess\Result\Exception\NoResultException.
    */
    $resultBuilder->getFirstOrDefaultResult(new Watch);
```

```php
    /*
    Return an array indexed by a property value (like "brand" value).

    If the index does not exists, throw an exception Kassko\DataAccess\Result\Exception\NotFoundIndexException.

    If the same index is found twice, throw an exception
    Kassko\DataAccess\Result\Exception\DuplicatedIndexException.
    */
    $resultBuilder->getResultIndexedByBrand();//Indexed by brand value
    //or
    $resultBuilder->getResultIndexedByColor();//Indexed by color value
```

```php
    /*
    Return an iterator.

    Result will not be hydrated immediately but only when you will iterate the results (with "foreach" for example).
    */
    $result = $resultBuilder->getIterableResult();
    foreach ($result as $object) {//$object is hydrated

        if ($object->getColor() === 'blue') {
            break;

            //We found the good object then we stop the loop and others objects in results will not be hydrated.
        }
    }
```

```php
    /*
    Return an iterator indexed by a property value (like "brand" value).

    If the index does not exists, throw an exception Kassko\DataAccess\Result\Exception\NotFoundIndexException.

    If the same index is found twice, throw an exception Kassko\DataAccess\Result\Exception\DuplicatedIndexException.
    */
    $resultBuilder->getIterableResultIndexedByBrand();
    //or
    $resultBuilder->getIterableResultIndexedByColor();
```

### How to get the ResultBuilderFactory instance ###

If you work with a framework, normally, you can get a ResultBuilderFactory instance from a container.
For example, with Symfony framework, we can use the [kassko/data-access-bundle](https://github.com/kassko/data-access-bundle) which provides to the container a ResultBuilderFactory service.

If you have no integration of data-access, you can use the [kassko/data-access-builder](https://github.com/kassko/data-access-builder). It will help you to get a ResultBuilderFactory instance.

### Features ###

#### toOne associations ####

```php
use Kassko\DataAccess\Annotation as DA;

class Keyboard
{
    /**
     * @DA\Field
     * @DA\Id
     */
    private $id;

    /**
     * @DA\Field
     */
    private $color;

    /**
     * @DA\ToOne(entityClass="Manufacturer", findMethod="find")
     * @DA\Field(name="manufacturer_id")
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

As you can guess, the "find" method is that of the repository of the entity "Manufacturer" meanning "ManufacturerManager::find()".

```php
/**
 * @DA\Object(repositoryClass="ManufacturerManager")
 */
class Manufacturer
{
    /**
     * @DA\Field
     * @DA\Id
     */
    private $id;

    /**
     * @DA\Field
     */
    private $name;

    public function getId() { return $this->id; }

    public function setId($id) { $this->id = $id; }

    public function getName() { return $this->name; }

    public function setName($name) { $this->name = $name; }
}
```

```php
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

Usage:
```php
$data = [
    'id' => 1,
    'color' => 'blue',
    'manufacturer_id' => 1
];

//Here some stuff to create $resultBuilderFactory

$resultBuilder = $resultBuilderFactory->create('Keyboard', $data);
var_dump($resultBuilder->getSingleResult());
```

Display result:
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

If the repository class wherin we wants to fetch is not that of the entity, we can override it:

```php
use Kassko\DataAccess\Annotation as DA;

class Keyboard
{
    /**
     * @DA\ToOne(entityClass="Manufacturer", repositoryClass="UnconventionnalManager" findMethod="find")
     * @DA\Field
     */
    private $manufacturer;
}
```
The find method will be "UnconventionnalManager::find()" instead of "ManufacturerManager::find()".

Note that in an entity, if no property is specified as the identifier, the default identifier is a property named "$id". And if no property "$id" exists, an exception is thrown when the system attempt to know the entity identifier.

Note also that for performance reasons, we can load the association "$manufacturer" only when we use it. For more details see the "Lazy loading" section.

#### toMany associations ####

An association "to many" is used similarly to "to one".

```php
use Kassko\DataAccess\Annotation as DA;

class Keyboard
{
    /**
     * @DA\Field
     * @DA\Id
     */
    private $id;

    /**
     * @DA\Field
     */
    private $color;

    /**
     * @DA\Field
     * @DA\ToMany(entityClass="Shop", findMethod="findByKeyboard")
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
/**
 * @DA\Object(repositoryClass="ShopManager")
 */
class Shop
{
    /**
     * @DA\Id
     * @DA\Field
     */
    private $id;

    /**
     * @DA\Field
     */
    private $name;

    public function getId() { return $this->id; }

    public function setId($id) { $this->id = $id; }

    public function getName() { return $this->name; }

    public function setName($name) { $this->name = $name; }
}
```

The association name is the entity class name not full qualified. So if "Shop" is the entity class name, then "Shop" is the association name and then in "Keyboard" entity the "adder" is "addShop()".

If the "Shop" FQCN (full qualified class name) was "Kassko\Sample\Shop", the association name would had been "Shop" and the adder would had been "addShop()".

But you can override this association name:
```php
use Kassko\DataAccess\Annotation as DA;

class Keyboard
{
    /**
     * @DA\ToMany(name="insertShop", entityClass="Shop", findMethod="find")
     * @DA\Field
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

//Here some stuff to create $resultBuilderFactory

$resultBuilder = $resultBuilderFactory->create($data, 'Keyboard');
var_dump($resultBuilder->getSingleResult());
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

#### Providers ####
A provider is usefull to create a super object. That is to say an object wich contains other objects or some collections but there is no relation with these objects.

```php

class Information
{
    /**
     * @DA\Provider(class="Kassko\Samples\KeyboardManager", method="loadKeyboards")
     * @DA\Field
     */
    private $keyboards = [];

    /**
     * @DA\Provider(class="Kassko\Samples\ShopManager", method="loadBestShop")
     * @DA\Field
     */
    private $bestShop;

    public function setBestShop(Shop $shop) { $this->bestShop = $bestShop; }
    public function addShop(Keyboard $keyboard) { $this->keyboard[] = $keyboard; }
}

class Keyboard
{
    /**
     * @DA\Field
     */
    private $brand;

    /**
     * @DA\Field
     */
    private $color;
}

class Shop
{
    /**
     * @DA\Field
     */
    private $name;

    /**
     * @DA\Field
     */
    private $address;
}

class ShopManager
{
    public function loadBestShop(Information $info)
    {
        $info->setBestShop('shop 1');
    }
}

class KeyboardManager
{
    public function loadKeyboards(Information $info)
    {
        $info->addKeyboard(['keyboard 1']);
        $info->addKeyboard(['keyboard 2']);
    }
}
```
We also can load the properties "bestShop" and "keyboard" only when we use it. For more details see the "Lazy loading" section.

#### Lazy loading ####
This section will be written later.

#### Value object ####
This section will be written later.

#### Mapping inheritance ####
This section will be written later.

##### Field mapping level and class mapping level #####
If all fields use a same option, you can configure this option at "object" level.

##### Class mapping inheritance #####
This section will be written later.

##### Resource mapping inheritance #####
This section will be written later.

#### Other features ####

* You can cache your object.
[see more in cache reference documentation](https://github.com/kassko/data-access/blob/master/Resources/doc/cache.md).

* You can attach listeners to an action.
[see more in listener reference documentation](https://github.com/kassko/data-access/blob/master/Resources/doc/listener.md).

* You can use public properties instead of getters/setters.
[see more in public properties reference documentation](https://github.com/kassko/data-access/blob/master/Resources/doc/public_property.md).

* You can log in your object without injecting to it a logger dependency.
[see more in log reference documentation](https://github.com/kassko/data-access/blob/master/Resources/doc/log.md).

These features will be explained and detailled later.
