data-mapper
==================

[![Latest Stable Version](https://poser.pugx.org/kassko/data-mapper/v/stable.png)](https://packagist.org/packages/kassko/data-mapper)
[![Total Downloads](https://poser.pugx.org/kassko/data-mapper/downloads.png)](https://packagist.org/packages/kassko/data-mapper)
[![Latest Unstable Version](https://poser.pugx.org/kassko/data-mapper/v/unstable.png)](https://packagist.org/packages/kassko/data-mapper)

## Presentation ##

data-mapper component represents some raw data like objects as all data-mapper but it particularly allows to create complex representations. Use it:

* if you only want a mapper
* if you administrate your databases yourself
* if you need to use multiples data sources to hydrate an object (data-mapper is agnostic of data access infrastructure, it abstract it by requiring providers)
* to create representations with nested objects and several nesting levels (Person => Address => Street)
* to reuse an object with other mapping rules
* to prepare/transform some raw data before hydrating an object
* to prepare/transform object properties before retrieving the corresponding raw data
* to keep your objects agnostic of mapping
* to reuse or inherit some mapping configurations
* to choose your mapping configuration format or to use various formats in the same application

data-mapper requires providers. It means you can hydrate some properties from mysql, other properties from mssql and some relations from a webservice.

## Installation ##

Add to your composer.json:
```json
"require": {
    "kassko/data-mapper": "~0.8.0@alpha"
}
```

Note that:
* the second version number is used when compatibility is broken
* the third for new feature
* the fourth for hotfix
* the first for new API or to go from pre-release to release (from 0 to 1)

## Utilisation ##

### Mapping configuration ###

You create a mapping configuration (with annotations, yaml or php):

#### Annotations format ####
```php
namespace Kassko\Sample;

use Kassko\DataMapper\Annotation as DA;

class Watch
{
    /**
     * @DA\Field
     */
    private $brand;

    /**
     * @DA\Field(name="COLOR")
     */
    private $color;

    private $nice;//The field nice has not Field annotation because we don't want the mapper to hydrate it or to extract it

    public function getBrand() { return $this->brand; }
    public function setBrand($brand) { $this->brand = $brand; }
    public function getColor() { return $this->color; }
    public function setColor($color) { $this->color = $color; }
    public function isNice() { return $this->nice; }
    public function setNice($nice) { $this->nice = $nice; }
}
```

Or you create a yaml file mapping configuration:
#### Yaml file format ####
```yaml
fields:
    brand: ~ # this field hasn't got specific configuration but we want the mapper to manage it (it will be hydrated and extracted)
    color:
        name: "COLOR"

# The field nice don't appear in the field section because we don't want the mapper to manage it.
```

Or you create a php file mapping configuration:
#### Php file format ####
```php
return [
    'fields' => [
        'brand', //this field hasn't got specific configuration but we want the mapper to manage it (it will be hydrated and extracted)
        'color' => ['name' => 'COLOR'],
];

//The field nice don't appear in the field section because we don't want the mapper to manage it.
```

Or you create an inner php mapping configuration (the php config will be returned by the object itself):

#### Inner php format ####
```php
namespace Kassko\Sample;

class Watch
{
    private $brand;
    private $color;
    private $nice;

    public function getBrand() { return $this->brand; }
    public function setBrand($brand) { $this->brand = $brand; }
    public function getColor() { return $this->color; }
    public function setColor($color) { $this->color = $color; }
    public function isNice() { return $this->nice; }
    public function setNice($nice) { $this->nice = $nice; }

    public static function provideMapping()
    {
        return [
            'fields' => [
                'brand', //this field hasn't got specific configuration but we want the mapper to manage it (it will be hydrated and extracted)
                'color' => ['name' => 'COLOR'],
        ];

        //The field nice don't appear in the field section because we don't want the mapper to manage it.
    }
}
```

Or you create an inner yaml mapping configuration (the yaml config will be returned by the object itself):

#### Inner yaml format ####
```php
namespace Kassko\Sample;

class Watch
{
    private $brand;
    private $color;
    private $nice;

    public function getBrand() { return $this->brand; }
    public function setBrand($brand) { $this->brand = $brand; }
    public function getColor() { return $this->color; }
    public function setColor($color) { $this->color = $color; }
    public function isNice() { return $this->nice; }
    public function setNice($nice) { $this->nice = $nice; }

    public static function provideMapping()
    {
        return <<<EOF
fields:
    brand: ~ # this field hasn't got specific configuration but we want the mapper to manage it (it will be hydrated and extracted)
    color:
        name: "COLOR"

# The field nice don't appear in the field section because we don't want the mapper to manage it.
EOF;
    }
}
```

### API Usage ###

To do hydration and extraction operations, you get a DataMapper instance and you hydrate your object with its hydrator:
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

$dataMapper = (new Kassko\DataMapper\Factory)->instance();

$object = new Kassko\Sample\Watch;
$dataMapper->hydrator('Kassko\Sample\Watch')->hydrate($data, $object);
var_dump($object);
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
$dataMapper = (new Kassko\DataMapper\Factory)->instance();

$rawResult = $dataMapper->hydrator('Kassko\Sample\Watch')->extract($object);
var_dump($rawResult);
```

If you have several records, you need to hydrate a collection rather than a single object. To do that, you can use the ResultBuilder which allows you to choose in what your result should be contained:
```php
$data = [
    0 => [
        'brand' => 'some brand',
        'color' => 'blue',
        'created_date' => '2014 09 14 12:36:52',
        'waterProof' => '1',
        'stopWatch' => 'X',
        'customizable' => '0',
        'seal_date' => '',
    ],
    1 => [
        'brand' => 'some other brand',
        'color' => 'green',
        'created_date' => '2014 09 12 11:36:52',
        'waterProof' => '1',
        'stopWatch' => 'X',
        'customizable' => '0',
        'seal_date' => '',
    ],
];

$dataMapper = (new Kassko\DataMapper\Factory)->instance();

$dataMapper->resultBuilder('Kassko\Sample\Watch', $data)->all();//The result will an array with two objects.
$dataMapper->resultBuilder('Kassko\Sample\Watch', $data)->first();//The result will be the object representation of the first record.
```

Inversely, you can extract values of an object or a an object collection to have raw result:
```php
$dataMapper = (new Kassko\DataMapper\Factory)->instance();

$dataMapper->resultBuilder('Kassko\Sample\Watch')->raw($object);
$dataMapper->resultBuilder('Kassko\Sample\Watch')->raw($collection);
```

There are still other ways to get results:

### Ways to get results ###

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
    Return an array indexed by a property value (like "brand" value).

    If the index does not exists, throw an exception Kassko\DataMapper\Result\Exception\NotFoundIndexException.

    If the same index is found twice, throw an exception
    Kassko\DataMapper\Result\Exception\DuplicatedIndexException.
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
    Return an iterator indexed by a property value (like "brand" value).

    If the index does not exists, throw an exception Kassko\DataMapper\Result\Exception\NotFoundIndexException.

    If the same index is found twice, throw an exception Kassko\DataMapper\Result\Exception\DuplicatedIndexException.
    */
    $resultBuilder->iterableIndexedByBrand();
    //or
    $resultBuilder->iterableIndexedByColor();
```

### Api usage: Configure the DataMapper ###

The DataMapper has several settings. Its default settings are often appropriate but not everytime.

For example, if you want to use another mapping format than the default one ('annotation'), you need to configure the DataMapper before its instanciation:

```php
use Kassko\DataMapper\DataMapperFactory;

$dataMapper = (new DataMapperFactory)
    ->settings(
        [
            'default_resource_type' => 'yaml_file',
            'default_resource_dir' => 'c:\mapping',
            'object' =>
                [
                    [
                        'class' => 'Kassko\Sample\Watch'
                        'resource_path' => 'c:\some_project\mapping\watch.yml'
                    ],
                ]
        ]
    )
    ->instance()
;
```

Allows to use the yaml file format everywhere. And the files are in c:\mapping

```php
use Kassko\DataMapper\DataMapperFactory;

$dataMapper = (new DataMapperFactory)
    ->settings(
        [
            //'default_resource_type' => 'annotations',//By default, 'default_resource_type' is set to 'annotations'
            'default_resource_dir' => 'c:\mapping'
            'mapping' =>
            [
                'object' =>
                [
                    [
                        'class' => 'Kassko\Sample\Watch'
                        'resource_type' => 'yaml_file'
                        'resource_path' => 'c:\some_project\mapping\watch.yml'
                    ],
                ],
                'object' =>
                [
                    [
                        'class' => 'Kassko\Sample\Keyboard'
                    ],
                ],
            ]
        ]
    )
    ->instance()
;

Here, you use the yaml file format only for Kassko\Sample\Watch
For all others objects, you use annotations.
```

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
            'instance' => 'Kassko\Cache\ArrayCache',

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

### More advanced mapping configuration ###

You create a mapping configuration (with annotations, yaml or php):

#### Annotations format ####
```php
namespace Kassko\Sample;

use Kassko\DataMapper\Annotation as DA;
use Kassko\DataMapper\Hydrator\HydrationContextInterface;
use Kassko\DataMapper\Hydrator\Value;
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
     * @DA\Field(readStrategy="hydrateBoolFromSymbol", writeStrategy="extractBoolToSymbol")
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

    public static function hydrateBoolFromSymbol(Value $value, HydrationContextInterface $context)
    {
        $value->value = $value->value == 'X';
    }

    public static function extractBoolToSymbol(Value $value, HydrationContextInterface $context)
    {
        $value->value = $value->value ? 'X' : ' ';
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

You can keep your object agnostic of mapping if you use one of the outer mapping format and put your callbacks in a mapping extension class.

Your mapping file:
```yaml
object:
    fieldMappingExtensionClass: "Kassko\\Sample\\WatchMappingExtension"
    classMappingExtensionClass: "Kassko\\Sample\\WatchMappingExtension"
interceptors:
    postExtract: onAfterExtract
    postHydrate: onAfterHydrate
fields:
    brand:
        readStrategy: readBrand
        writeStrategy: writeBrand
    color: ~ # this field hasn't got specific configuration but we want the mapper manage it
    createdDate:
        name: created_date
        type: date
        readDateFormat: "Y-m-d H:i:s"
        writeDateFormat: "Y-m-d H:i:s"
    waterProof:
        readStrategy: hydrateBool
        writeStrategy: extractBool
    stopWatch:
        readStrategy: hydrateBoolFromSymbol
        writeStrategy: extractBoolToSymbol
        mappingExtensionClass: WatchCallbacks
    customizable:
        readStrategy: hydrateBool
        writeStrategy: extractBool
        getter: canBeCustomized
```

Your mapping extension class:
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

    public static function onAfterHydrate(HydrationContextInterface $context, Watch $object)
    {
        //Have a look at the signature method (you add the Watch object when you use an interceptor).

        if ('' === $context->getItem('seal_date')) {
            $value = $context->getItem('created_date');
            $object->setNoSealDate(true);
        } else {
            $value = $context->getItem('seal_date');
        }

        $object->setSealDate(DateTime::createFromFormat('Y-m-d H:i:s', $value));
    }

    public static function onAfterExtract(HydrationContextInterface $context, Watch $object)
    {
        //Have a look at the signature method (you add the Watch object when you use an interceptor).

        if (! $object->isThereSealDate()) {
            $context->setItem('seal_date', '');
        } else {
            $context->setItem('seal_date', $object->getSealDate()->format('Y-m-d H:i:s'));
        }
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

And your object cleaned:
```php
namespace Kassko\Sample;

use \DateTime;

class Watch
{
    private $brand;
    private $color;
    private $createdDate;
    private $waterProof;
    private $stopWatch;
    private $customizable;
    private $sealDate;
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
    public function isThereSealDate() { return ! $this->noSealDate; }
    public function setNoSealDate($nosealDate) { $this->noSealDate = $noSealDate; }
}
```

As you can see, your object is not aware of mapping.

For more details about mapping you can read the mapping reference documentations:

#### Inner yaml format ####
[see inner Yaml mapping reference documentation](https://github.com/kassko/data-mapper/blob/master/Resources/doc/inner_yaml_mapping.md).

#### Yaml file format ####
[see yaml mapping reference documentation](https://github.com/kassko/data-mapper/blob/master/Resources/doc/yaml_file_mapping.md).

#### Inner php ####
[see inner php mapping reference documentation](https://github.com/kassko/data-mapper/blob/master/Resources/doc/inner_php_mapping.md).

#### Php file format ####
[see Php mapping reference documentation](https://github.com/kassko/data-mapper/blob/master/Resources/doc/php_file_mapping.md).

As you can see,
* You can transform raw data before hydration or object values before extraction.
* You can isolate transformation methods in a separated file (see the mapping extension class WatchCallbacks). So to keep your entity agnostic of mapping use one of the outer mapping format and put your transformations in a mapping extension class.
* You can convert a date before hydrating or extracting it.
* Isser (see isWaterProof()) and has methods (see hasStopWatch()) are managed.
* But you can specify custom getter/setter (see canBeCustomized()).

### Features ###

#### toOneProvider associations ####

```php
namespace Kassko\Sample;

use Kassko\DataMapper\Annotation as DA;

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
     * @DA\ToOneProvider(entityClass="Kassko\Sample\Manufacturer", findMethod="find")
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
namespace Kassko\Sample;

/**
 * @DA\Object(repositoryClass="Kassko\Sample\ManufacturerManager")
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

Usage:
```php
$data = [
    'id' => 1,
    'color' => 'blue',
    'manufacturer_id' => 1
];

//Here some stuff to create $dataMapper

var_dump($dataMapper->resultBuilder('Kassko\Sample\Keyboard', $data)->single());
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
namespace Kassko\Sample;

use Kassko\DataMapper\Annotation as DA;

class Keyboard
{
    /**
     * @DA\ToOneProvider(entityClass="Kassko\Sample\Manufacturer", repositoryClass="UnconventionnalManager" findMethod="find")
     * @DA\Field
     */
    private $manufacturer;
}
```
The find method will be "UnconventionnalManager::find()" instead of "ManufacturerManager::find()".

Note that in an entity, if no property is specified as the identifier, the default identifier is a property named "$id". And if no property "$id" exists, an exception is thrown when the system attempt to know the entity identifier.

Note also that for performance reasons, we can load the association "$manufacturer" only when we use it. For more details see the "Lazy loading" section.

#### toManyProvider associations ####

An association "to many" is used similarly to "to one".

```php
namespace Kassko\Sample;

use Kassko\DataMapper\Annotation as DA;

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
     * @DA\ToManyProvider(entityClass="Kassko\Sample\Shop", findMethod="findByKeyboard")
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
 * @DA\Object(repositoryClass="Kassko\Sample\ShopManager")
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
namespace Kassko\Sample;

use Kassko\DataMapper\Annotation as DA;

class Keyboard
{
    /**
     * @DA\ToManyProvider(name="insertShop", entityClass="Kassko\Sample\Shop", findMethod="find")
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

#### Providers ####
A provider is usefull to create a super object. That is to say an object which contains other objects or some collections but there is no relation with these objects.

```php
namespace Kassko\Sample;

class Information
{
    /**
     * @DA\Provider(class="Kassko\Sample\KeyboardManager", method="loadKeyboards")
     * @DA\Field
     */
    private $keyboards = [];

    /**
     * @DA\Provider(class="Kassko\Sample\ShopManager", method="loadBestShop")
     * @DA\Field
     */
    private $bestShop;

    public function setBestShop(Shop $shop) { $this->bestShop = $bestShop; }
    public function addShop(Keyboard $keyboard) { $this->keyboard[] = $keyboard; }
}
```

```php
namespace Kassko\Sample;

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
```

```php
namespace Kassko\Sample;

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
```

```php
namespace Kassko\Sample;

class ShopManager
{
    public function loadBestShop(Information $info)
    {
        $info->setBestShop((new Shop())->setName('The best')->setAddress('3 best street'));
    }
}
```

```php
namespace Kassko\Sample;

class KeyboardManager
{
    public function loadKeyboards(Information $info)
    {
        $info->addKeyboard((new Keyboard())->setBrand('Some brand')->setColor('blue'));
        $info->addKeyboard((new Keyboard())->setBrand('Another brand')->setColor('green'));
    }
}
```
We also can load the properties "bestShop" and "keyboard" only when we use it. For more details see the "Lazy loading" section.

#### Lazy loading ####

You can lazy load associations ToOneProvider:

```php
namespace Kassko\Sample;

use Kassko\DataMapper\Annotation as DA;
use Kassko\DataMapper\ObjectExtension\LazyLoadableTrait;

class Keyboard
{
    use LazyLoadableTrait;

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
     * @DA\ToOneProvider(entityClass="Kassko\Sample\Manufacturer", findMethod="find", lazyLoading="true")
     * @DA\Field(name="manufacturer_id")
     */
    private $manufacturer;

    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id; }
    public function getColor() { return $this->color; }
    public function setColor($color) { $this->color = $color; }

    public function getManufacturer()
    {
        $this->loadProperty('manufacturer');//<= Load the manufacturer if not loaded.
        return $this->manufacturer;
    }

    public function setManufacturer(Manufacturer $manufacturer)
    {
        $this->manufacturer = $manufacturer;
    }
}
```

And ToManyProvider:

```php
namespace Kassko\Sample;

use Kassko\DataMapper\Annotation as DA;
use Kassko\DataMapper\ObjectExtension\LazyLoadableTrait;

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
     * @DA\ToManyProvider(entityClass="Kassko\Sample\Shop", findMethod="findByKeyboard", lazyLoading="true")
     */
    private $shops;

    public function __construct() { $this->shops = []; }

    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id; }
    public function getColor() { return $this->color; }
    public function setColor($color) { $this->color = $color; }

    public function getShops()
    {
        $this->loadProperty('shops');//<= Load the manufacturer if not loaded.
        return $this->shops;
    }

    public function addShop(Shop $shop) { $this->shops[] = $shops; }
}
```

And you can "lazy provide":
```php
namespace Kassko\Sample;

class Information
{
    /**
     * @DA\Provider(class="Kassko\Sample\KeyboardManager", method="loadKeyboards", lazyLoading="true")
     * @DA\Field
     */
    private $keyboards = [];

    /**
     * @DA\Provider(class="Kassko\Sample\ShopManager", method="loadBestShop", lazyLoading="true")
     * @DA\Field
     */
    private $bestShop;

    public function setBestShop(Shop $shop) { $this->bestShop = $bestShop; }
    public function addShop(Keyboard $keyboard) { $this->keyboard[] = $keyboard; }
}
```

#### Use the same model with various mapping configuration ####

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

#### Value object ####

```php
namespace Kassko\Sample;

use Kassko\DataMapper\Annotation as DA;

class Customer
{
    /**
     * @DA\Field
     * @DA\Id
     */
    private $id;

    /**
     * @DA\Field
     * @DA\ValueObject(class="Kassko\Sample\Address", mappingResourceType="yaml", mappingResourceName="billing_address.yml")
     */
    private $billingAddress;//$billingAddress is a value object.

    /**
     * @DA\Field
     * @DA\ValueObject(class="Kassko\Sample\Address", mappingResourceType="yaml", mappingResourceName="shipping_address.yml")
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
[see more in cache reference documentation](https://github.com/kassko/data-mapper/blob/master/Resources/doc/cache.md).

* You can attach listeners to an action.
[see more in listener reference documentation](https://github.com/kassko/data-mapper/blob/master/Resources/doc/listener.md).

* You can use public properties instead of getters/setters.
[see more in public properties reference documentation](https://github.com/kassko/data-mapper/blob/master/Resources/doc/public_property.md).

* You can log in your object without injecting to it a logger dependency.
[see more in log reference documentation](https://github.com/kassko/data-mapper/blob/master/Resources/doc/log.md).

These features will be explained and detailled later.

### Api details ###

Normally, if you work with a framework which integrates the component data-mapper, you can get a DataMapper instance from a container.
For example, with Symfony framework, we can use the [kassko/data-mapper-bundle](https://github.com/kassko/data-mapper-bundle) which provides to the container a DataMapper service.

Otherwise you need to create it yourself.


#### Create an adapter for the cache ####
Instead of use Kassko\DataMapper\Cache\ArrayCache, you can provide a cache adapter. For example, you can use the Kassko cache interface with a Doctrine cache implementation or a Winzou cache implementation.

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
