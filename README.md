data-access
==================

data-access help you to represent some data like objects and work with objects.


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

You hydrate an object with this result set:
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
     * @OM\Column(readStrategy="hydrateBool", writeStrategy="extractBool", getter="canBeCustomized")
     */
    private $customizable;

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
        $value->value = ! isset($this->brandCodeToLabelMap[$value->value]) ?: $this->brandCodeToLabelMap[$value->value];
    }

    public function writeBrand(Value $value, HydrationContextInterface $context)
    {
        $value->value = ! isset(self::brandLabelToCodeMap[$value->value]) ?: self::brandLabelToCodeMap[$value->value];
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

        $this->sealDate = DateTime::createFromFormat('Y-m-d', $value);
    }

    /**
     * @OM\PostExtract
     */
    public function onAfterExtract(HydrationContextInterface $context)
    {
        if ($this->noSealDate) {
            $context->setItem('seal_date', '');
        } else {
            $context->setItem('seal_date', $this->sealDate->format('Y-m-d'));
        }
    }
}
```

This result set:
```php
[
    'brand' => 'some brand',
    'COLOR' => 'blue',
    'created_date' => '2014 09 14 12:36:52',
    'waterProof' => '1',
    'stopWatch' => 'X',
    'customizable' => '0',
    'seal_date' => ''
]
```

Will be transform like that:
```php
object(Watch)
{
    ["brand":"Watch":private]=> string(10) "some brand"
    ["color":"Watch":private]=> string(4) "blue"
    ["createdDate":"Watch":private]=> object (DateTime) {"2014 09 14 12:36:52"}
    ["waterProof":"Watch":private]=> bool "true"
    ["stopWatch":"Watch":private]=> bool "true"
    ["customizable":"Watch":private]=> bool "false"
    ["sealDate":"Watch":private]=> object (DateTime) {"2014 09 14 12:36:52"}
}
```

As you can see,
* You can process a customized property hydration or extraction.
* You can convert a date before hydrating or extracting it.
* Isser (isWaterProof()) and has methods (hasStopWatch()) are handled.
* But you can specify custom getter/setter (canBeCustomized()).

There are a lot of other features.

* You can build an object from several sources.
Imagine an object with a property hydrated from SqlServer, an other from Elastic search, an other from a web service, and an other from MongoDb:

* You can lazy load properties.

* You can load asociations.

* You can cache your object.

* You can attach listeners to an action.

* If all colum use a same option, you configure this option in "entity" annotation. This annotation is placed at the object level.

* You can use public properties instead of getters/seters.

* You can map value objects.

* You can use Yaml format if you prefer this one to annotations.

* You can log in your object without injecting to it a logger dependency.

These features will be explained and detailled later.

Api utilization
---------------

This section will be written later.