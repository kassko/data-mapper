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
    "kassko/data-mapper": "~0.12.2"
}
```

Note that:
* the second version number is used when compatibility is broken
* the third for new feature
* the fourth for hotfix
* the first for new API or to go from pre-release to release (from 0 to 1)

### Basic usage ###

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

To know more about `kassko/data-mapper`:
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



