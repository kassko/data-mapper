data-access
==================

[![Latest Stable Version](https://poser.pugx.org/kassko/data-access/v/stable.png)](https://packagist.org/packages/kassko/data-access)
[![Total Downloads](https://poser.pugx.org/kassko/data-access/downloads.png)](https://packagist.org/packages/kassko/data-access)
[![Latest Unstable Version](https://poser.pugx.org/kassko/data-access/v/unstable.png)](https://packagist.org/packages/kassko/data-access)

data-access component is a mapper which gives a lot of flexibility to representate some raw data like objects. Use it if you need:

* to keep your objects agnostic and preserve their base class (data-access implements Data Mapper pattern and not Active Record)
* to transform raw data before hydrating an object
* to create representations with nested objects and several nesting levels (Person => Address => Street)
* to map value objects (Address, Street) and reuse them with other mapping rules
* to reuse and inherit the mapping configurations
* to choose your mapping configuration format or to use various formats in the same application


Installation
---------------

Add to your composer.json:
```json
"require": {
    "kassko/data-access": "dev-master"
}
```

Presentation
---------------

You have a result set:
```php
[
    'brand' => 'some brand',
    'COLOR' => 'blue'
]
```

You hydrate an object from this result set:
```php
object(Watch) (2)
{
    ["brand":"Watch":private]=> string(10) "some brand" ["color":"Watch":private]=> string(4) "blue"
}
```

And inversely, you extract your object properties to have raw result.

To do that, you annotate your entity:
```php
use Kassko\DataAccess\Annotation as OM;

class Watch
{
    /**
     * @OM\Field
     */
    private $brand;

    /**
     * @OM\Field(name="COLOR")
     */
    private $color;//Here, storage field name is different of object field name.

    public function getBrand()
    {
        return $this->brand;
    }

    public function setBrand($brand)
    {
        $this->brand = $brand;
    }

    public function getColor()
    {
        return $this->color;
    }

    public function setColor($color)
    {
        $this->color = $color;
    }
}
```

A property without "Field" annotation is not managed (no hydrated and no extracted).

And you get a ResultBuilderFactory instance:

If you work with a framework, normally, you can get a ResultBuilderFactory instance from a container.
For example, with Symfony framework, we can use the [kassko/data-access-bundle](https://github.com/kassko/data-access-bundle) which provides to the container a ResultBuilderFactory service.

If you have no integration of data-access, you can use the [kassko/data-access-builder](https://github.com/kassko/data-access-builder). It will help you to get a ResultBuilderFactory instance.

And you hydrate your object:
```php
$data = [
    'brand' => 'some brand',
    'COLOR' => 'blue'
];

$resultBuilder = $resultBuilderFactory->create('Watch', $data);
$result = $resultBuilder->getResult();
var_dump($result);
```

The code above will display:
```php
array(1) {
    [0]=>
    object(Watch)#283 (8) {
        ["brand":"Watch":private]=> string(10) "some brand"
        ["color":"Watch":private]=> string(4) "blue"
    }
}
```

Inversely, you can extract values from your object:
```php
$keyBoard = (new Watch)
    ->setBrand('some brand')
    ->setColor('blue')
;

$resultBuilder = $resultBuilderFactory->create('Watch');
$result = $resultBuilder->getRawResult();
var_dump($result);
```

The code above will display:
```php
array(2) {
  ['brand']=>
  'some brand'
  ['COLOR']=>
  'blue'
  }
}
```

As you can see, the getResult() method return the object in an array. Maybe you would prefer to get the object instead of an array containing this object. There are severals ways to get results:

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

You can do more advanced mapping:
```php

use Kassko\DataAccess\Annotation as OM;
use Kassko\DataAccess\Hydrator\HydrationContextInterface;
use Kassko\DataAccess\Hydrator\Value;
use \DateTime;

class Watch
{
    private static $brandCodeToLabelMap = [1 => 'Brand A', 2 => 'Brand B'];
    private static $brandLabelToCodeMap = ['Brand A' => 1, 'Brand B' => 2];

    /**
     * @OM\Field(readStrategy="readBrand", writeStrategy="writeBrand")
     */
    private $brand;

    /**
     * @OM\Field
     */
    private $color;

    /**
     * @OM\Field(name="created_date", type="date", readDateFormat="Y-m-d H:i:s", writeDateFormat="Y-m-d H:i:s")
     */
    private $createdDate;

    private $sealDate;

    /**
     * @OM\Field(readStrategy="hydrateBool", writeStrategy="extractBool")
     */
    private $waterProof;

    /**
     * @OM\Field(readStrategy="hydrateBoolFromSymbol", writeStrategy="extractBoolToSymbol")
     */
    private $stopWatch;

    /**
     * @OM\Field(readStrategy="hydrateBool", writeStrategy="extractBool")
     * @OM\Getter(name="canBeCustomized")
     */
    private $customizable;//Naturally, we also can customize setters with "Setter" annotation.

    private $noSealDate = false;

    public function getBrand()
    {
        return $this->brand;
    }

    public function setBrand($brand)
    {
        $this->brand = $brand;
    }

    public function getColor()
    {
        return $this->color;
    }

    public function setColor($color)
    {
        $this->color = $color;
    }

    public function getCreatedDate()
    {
        return $this->createdDate;
    }

    public function setCreatedDate(DateTime $createdDate)
    {
        $this->createdDate = $createdDate;
    }

    public function isWaterProof()
    {
        return $this->waterProof;
    }

    public function setWaterProof($waterProof)
    {
        $this->waterProof = $waterProof;
    }

    public function hasStopWatch()
    {
        return $this->stopWatch;
    }

    public function setStopWatch($stopWatch)
    {
        $this->stopWatch = $stopWatch;
    }

    public function canBeCustomized()
    {
        return $this->customizable;
    }

    public function setCustomizable($customizable)
    {
        $this->customizable = $customizable;
    }

    public function getSealDate()
    {
        return $this->sealDate;
    }

    public function setSealDate(DateTime $sealDate)
    {
        $this->sealDate = $sealDate;
    }

    public function readBrand(Value $value, HydrationContextInterface $context)
    {
        if (isset(self::$brandCodeToLabelMap[$value->value])) {
            $value->value = self::$brandCodeToLabelMap[$value->value];
        }
    }

    public function writeBrand(Value $value, HydrationContextInterface $context)
    {
        if (isset(self::$brandLabelToCodeMap[$value->value])) {
            $value->value = self::$brandLabelToCodeMap[$value->value];
        }
    }

    public function hydrateBool(Value $value, HydrationContextInterface $context)
    {
        $value->value = $value->value == '1';
    }

    public function extractBool(Value $value, HydrationContextInterface $context)
    {
        $value->value = $value->value ? '1' : '0';
    }

    public function hydrateBoolFromSymbol(Value $value)
    {
        $value->value = $value->value == 'X';
    }

    public function extractBoolToSymbol(Value $value)
    {
        $value->value = $value->value ? 'X' : ' ';
    }

    /**
     * @OM\PostHydrate
     */
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

    /**
     * @OM\PostExtract
     */
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

This result set:
```php
[
    'brand' => 'some brand',
    'color' => 'blue',
    'created_date' => '2014 09 14 12:36:52',
    'waterProof' => '1',
    'stopWatch' => 'X',
    'customizable' => '0',
    'seal_date' => ''
]
```

Will be transform like that:
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

As you can see,
* You can customize property hydration or property extraction.
* You can convert a date before hydrating or extracting it.
* Isser (isWaterProof()) and has methods (hasStopWatch()) are handled.
* But you can specify custom getter/setter (canBeCustomized()).

There is a lot of others features.

* You can work with associations.
[see more in relation reference documentation](https://github.com/kassko/data-access/blob/master/Resources/doc/association.md).

* You can build an object from several sources.
Imagine an object which requires multiple sources fetching to represent it (SqlServer, Elastic search, Web Service, MongoDb).
[see more in provider reference documentation](https://github.com/kassko/data-access/blob/master/Resources/doc/provider.md).

* You can lazy load properties.
[see more in lazy loading reference documentation](https://github.com/kassko/data-access/blob/master/Resources/doc/lazy_loading.md).

* You can cache your object.
[see more in cache reference documentation](https://github.com/kassko/data-access/blob/master/Resources/doc/cache.md).

* You can attach listeners to an action.
[see more in listener reference documentation](https://github.com/kassko/data-access/blob/master/Resources/doc/listener.md).

* If all columns use a same option, you configure this option in "entity" annotation. This annotation is placed at the object level.
[see more in entity annotation documentation](https://github.com/kassko/data-access/blob/master/Resources/doc/entity_annotation.md).

* You can use public properties instead of getters/setters.
[see more in public properties reference documentation](https://github.com/kassko/data-access/blob/master/Resources/doc/public_property.md).

* You can map value objects.
[see more in value objets reference documentation](https://github.com/kassko/data-access/blob/master/Resources/doc/value_object.md).

* You can use Yaml format if you prefer this one rather than annotations.
[see more in yaml reference documentation](https://github.com/kassko/data-access/blob/master/Resources/doc/yaml.md).

* You can log in your object without injecting to it a logger dependency.
[see more in log reference documentation](https://github.com/kassko/data-access/blob/master/Resources/doc/log.md).

These features will be explained and detailled later.

Api usage
---------------

This section will be written later.