data-access
==================

data-access
* help you to represent some data like objects and work with objects.
* simplify integration of multiple data sources (mssql, mysql, nosql databases, webservices ...)


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

And inversely, you extract your object properties to have a raw result set.

To do that, you annotate your entity:
```php
use Kassko\DataAccess\Annotation as OM;

class Watch
{
    /**
     * @OM\Column
     */
    private $brand;

    /**
     * @OM\Column(name="COLOR")
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

A property without "Column" annotation is not managed (no hydrated and no extracted).

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
     * @OM\Column(readStrategy="readBrand", writeStrategy="writeBrand")
     */
    private $brand;

    /**
     * @OM\Column
     */
    private $color;

    /**
     * @OM\Column(name="created_date", type="date", readDateFormat="Y-m-d H:i:s", writeDateFormat="Y-m-d H:i:s")
     */
    private $createdDate;

    private $sealDate;

    /**
     * @OM\Column(readStrategy="hydrateBool", writeStrategy="extractBool")
     */
    private $waterProof;

    /**
     * @OM\Column(readStrategy="hydrateBoolFromSymbol", writeStrategy="extractBoolToSymbol")
     */
    private $stopWatch;

    /**
     * @OM\Column(readStrategy="hydrateBool", writeStrategy="extractBool")
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
object(Solfa\Bundle\ScalesBundle\Scales\Watch)#283 (8) {
    ["brand":"Solfa\Bundle\ScalesBundle\Scales\Watch":private]=> string(10) "some brand"
    ["color":"Solfa\Bundle\ScalesBundle\Scales\Watch":private]=> string(4) "blue"
    ["createdDate":"Solfa\Bundle\ScalesBundle\Scales\Watch":private]=>
        object(DateTime)#320 (3) { ["date"]=> string(19) "2014-09-14 12:36:52" ["timezone_type"]=> int(3) ["timezone"]=> string(13) "Europe/Berlin" }
    ["sealDate":"Solfa\Bundle\ScalesBundle\Scales\Watch":private]=>
        object(DateTime)#319 (3) { ["date"]=> string(19) "2014-09-14 12:36:52" ["timezone_type"]=> int(3) ["timezone"]=> string(13) "Europe/Berlin" }
    ["waterProof":"Solfa\Bundle\ScalesBundle\Scales\Watch":private]=> bool(true) ["stopWatch":"Solfa\Bundle\ScalesBundle\Scales\Watch":private]=> bool(true)
    ["customizable":"Solfa\Bundle\ScalesBundle\Scales\Watch":private]=> bool(false) ["noSealDate":"Solfa\Bundle\ScalesBundle\Scales\Watch":private]=> bool(true)
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
Imagine an object witch requires multiple sources fetching to represent it (SqlServer, Elastic search, Web Service, MongoDb).
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