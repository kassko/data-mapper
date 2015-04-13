data-mapper
==================

[![Build Status](https://secure.travis-ci.org/kassko/data-mapper.png?branch=master)](https://travis-ci.org/kassko/data-mapper)
[![Latest Stable Version](https://poser.pugx.org/kassko/data-mapper/v/stable.png)](https://packagist.org/packages/kassko/data-mapper)
[![Total Downloads](https://poser.pugx.org/kassko/data-mapper/downloads.png)](https://packagist.org/packages/kassko/data-mapper)
[![Latest Unstable Version](https://poser.pugx.org/kassko/data-mapper/v/unstable.png)](https://packagist.org/packages/kassko/data-mapper)

# Presentation #

data-mapper component represents some raw data like objects as all data-mapper but it particularly allows to create complex representations.


# Installation #

Add to your composer.json:
```json
"require": {
        "kassko/data-mapper": "~0.12.4"
}
```

Note that:
* the second version number is used when compatibility is broken
* the third for new feature
* the fourth for hotfix
* the first for new API or to go from pre-release to release (from 0 to 1)

* [Basicusage](#basic-usage)
* [ResultBuilder](#result-builder)
* [Enforcetypeoffields](#enforce-fields-type)
* [Apply converters before hydration or extraction](#converters)
  - [Converter](#converter)
  - [Date converter](#date-converter)
* [Add callbacks before or after hydration process](#callbacks)
* [Personalise getters and setters](#getters-setters)
* [Hydrate nested objects](#hydrate-nested-objects)
* [Configure a php object hydrator instead of a mapping configuration](#php-object-hydrator)
* [Work with object complex to create like service](#service-mapping-config)
* [Work with other mapping configuration format](#other-mapping-config)
  - [Outer format](#outer-mapping-config)
  - [Inner format](#inner-mapping-config)
* [Improve persistance ignorance](#persistance-ignorance)
* [Choose a mapping configuration at runtime](#runtime-mapping-config)
* [Bind a mapping configuration to a property especially](#config-prop-level)
* [Bind a source to a property or a set of properties / hydrate object from multi-sources, multi-orm](#bind-source-property)
    - [Provider](#provider)
    - [Data source](#data-source)
    - [Method arguments](#method-arg)
    - [Lazy loading](#lazy-loading)
    - [Source store](#source-store)
    - [Fallbacks sources](#fallback-sources)
    - [Processors](#processors)
* [Work with relations](#relations)
* [How to have DataMapper/ResultBuilder ignorance in the client code](#data-mapper-ignorance)
* [Use expression language](#expression-language)
    - [Basic usage of expression language](#expression-language-basic)
    - [Add a function provider](#add-provider)
* [Object listener](#object-listener)
* [Add a custom mapping configuration format](#custom-mapping-config)
* [Inherit mapping configuration](#inherit-mapping-config)
* [Data mapper configuration reference](#config-reference)
* [Mapping configuration reference](#mapping-config-reference)
    - [Annotations](#annotations-mapping-config)
    - [Yaml](#yaml-mapping-config)
    - [Php](#php-mapping-config)




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

Add callbacks before or after hydration process
-----------

Personalise getters and setters
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

Configure a php object hydrator instead of a mapping configuration
-----------

Work with object complex to create, like service
-----------

Work with other mapping configuration format
-----------

### Outer format

### Inner format

Improve persistance ignorance
-----------

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

Bind a source to a property or a set of properties / hydrate object from multi-sources, multi-orm
-----------

### Provider

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
 * @DM\DataSourceStore({
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

### Fallbacks sources

Here, sourceB replace sourceA if its not stable:

```php
namespace Kassko\Sample;

use Kassko\DataMapper\Annotation as DM;

/**
 * @DM\DataSourceStore({
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
 * @DM\DataSourceStore({
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

### Processors

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

Work with relations
-----------

How to have DataMapper/ResultBuilder ignorance in the client code
-----------

Use expression language
-----------

### Basic usage of expression language

### Add a function provider

Object listener
-----------

Add a custom mapping configuration format
-----------

Inherit mapping configuration
-----------

Data mapper configuration reference
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

### Annotations

### Yaml

### Php

========================================

* [`Result builder in details`](https://github.com/kassko/data-mapper/blob/master/Resources/doc/result_builder.md)
* [`Map some property names to keys of your raw datas`](https://github.com/kassko/data-mapper/blob/master/Resources/doc/map_properties.md)
* [`Convert some values before hydration or before extraction`](https://github.com/kassko/data-mapper/blob/master/Resources/doc/converters.md)
* [`Use a data source for a property`](https://github.com/kassko/data-mapper/blob/master/Resources/doc/data_source.md)
* [Lazy load some properties`](https://github.com/kassko/data-mapper/blob/master/Resources/doc/lazy_loading.md)
* [`Use a data source for a set of properties`](https://github.com/kassko/data-mapper/blob/master/Resources/doc/data_source.md)
* [`Hydrate nested object or nested collections`](https://github.com/kassko/data-mapper/blob/master/Resources/doc/nested_object_hydration.md)
* [`Use fallback data sources`](https://github.com/kassko/data-mapper/blob/master/Resources/doc/fallback_source.md)
* [`Use a provider`](https://github.com/kassko/data-mapper/blob/master/Resources/doc/provider.md)
* [`Use value objects`](https://github.com/kassko/data-mapper/blob/master/Resources/doc/value_objects.md)
* [`Select the fields to map`](https://github.com/kassko/data-mapper/blob/master/Resources/doc/field_exclusion_policy.md)
* [`Getters, isser, hasser and more get methods`](https://github.com/kassko/data-mapper/blob/master/Resources/doc/getter_setter.md)
* [`Choose or change an object mapping configurations at runtime`](https://github.com/kassko/data-mapper/blob/master/Resources/doc/runtime_configuration.md)
* [`Choose your mapping configuration format`](https://github.com/kassko/data-mapper/blob/master/Resources/doc/mapping_format.md)
* [`Configuration reference documentation`](https://github.com/kassko/data-mapper/blob/master/Resources/doc/configuration.md)
* [`Inner Yaml mapping reference documentation`](https://github.com/kassko/data-mapper/blob/master/Resources/doc/inner_yaml_mapping.md).
* [`Yaml mapping reference documentation`](https://github.com/kassko/data-mapper/blob/master/Resources/doc/yaml_file_mapping.md).
* [`Inner php mapping reference documentation`](https://github.com/kassko/data-mapper/blob/master/Resources/doc/inner_php_mapping.md).
* [`Php mapping reference documentation`](https://github.com/kassko/data-mapper/blob/master/Resources/doc/php_file_mapping.md).



