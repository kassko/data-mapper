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
    "kassko/data-mapper": "~0.12.0"
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

use Kassko\DataMapper\Annotation as DM;

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
     * DM\Fields(name="COLOR")
     */
    private $color;

    public function getBrand() { return $this->brand; }
    public function setBrand($brand) { $this->brand = $brand; }
    public function getColor() { return $this->color; }
    public function setColor($color) { $this->color = $color; }
}
```

By default, all fields are managed (hydrated and extracted). You can ignore some fields with the annotation Exclude.

```php
namespace Kassko\Sample;

use Kassko\DataMapper\Annotation as DM;

class Watch
{
    /**
     * DM\Exclude
     */
    private $brand;//Is not managed because of Exclude annotation.

    /**
     * DM\Fields(name="COLOR")
     */
    private $color;//Is managed because of fieldExclusionPolicy tuned by default to "include_all".

    public function getBrand() { return $this->brand; }
    public function setBrand($brand) { $this->brand = $brand; }
    public function getColor() { return $this->color; }
    public function setColor($color) { $this->color = $color; }
}
```

You can switch the behaviour such as it ignores all fields by default.

```php
namespace Kassko\Sample;

use Kassko\DataMapper\Annotation as DM;

/**
 * DM\Object(fieldExclusionPolicy="exclude_all")
 */
class Watch
{
    private $brand;//Is not managed because of fieldExclusionPolicy tuned to "exclude_all".

    /**
     * DM\Include
     * DM\Fields(name="COLOR")
     */
    private $color;//Is managed because of Include annotation.

    public function getBrand() { return $this->brand; }
    public function setBrand($brand) { $this->brand = $brand; }
    public function getColor() { return $this->color; }
    public function setColor($color) { $this->color = $color; }
}
``` 

If not specify, fieldExclusionPolicy is tuned on "exclude_all".

To know more about `kassko/data-mapper`:
* [`Basic usage`](https://github.com/kassko/data-mapper/blob/master/README.md)
* [`Map some property names to keys of your raw datas`](https://github.com/kassko/data-mapper/blob/master/Resources/doc/map_properties.md)
* [`Specify your field exclusion policy`](https://github.com/kassko/data-mapper/blob/master/Resources/doc/field_exclusion_policy.md)
* [`Convert some values before hydration or before extraction`](https://github.com/kassko/data-mapper/blob/master/Resources/doc/converters.md)
* [`Hydrate nested object or nested collections`](https://github.com/kassko/data-mapper/blob/master/Resources/doc/nested_object_hydration.md)
* [`Use a data source`](https://github.com/kassko/data-mapper/blob/master/Resources/doc/data_source.md)
* [`Use a provider`](https://github.com/kassko/data-mapper/blob/master/Resources/doc/provider.md)
* [`Lazy load some fields`](https://github.com/kassko/data-mapper/blob/master/Resources/doc/lazy_loading.md)
* [`Result builder in details`](https://github.com/kassko/data-mapper/blob/master/Resources/doc/result_builder.md)
* [`Use value objects`](https://github.com/kassko/data-mapper/blob/master/Resources/doc/value_objects.md)
* [`Choose or change an object mapping configurations at runtime`](https://github.com/kassko/data-mapper/blob/master/Resources/doc/runtime_configuration.md)
* [`Choose your mapping configuration format`](https://github.com/kassko/data-mapper/blob/master/Resources/doc/mapping_format.md)

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

### You can attach listeners to an action ###
This section will be written later.

[see more in listener reference documentation](https://github.com/kassko/data-mapper/blob/master/Resources/doc/listener.md).

### You can log in your object without injecting to it a logger dependency ###
[see more in log reference documentation](https://github.com/kassko/data-mapper/blob/master/Resources/doc/log.md).

### Use a service without injecting a dependency in your object ###
This section will be written later.

### API details ###

#### Create a ClassResolver instance ####
To know more about ClassResolver, see [the class-resolver documentation](https://github.com/kassko/class-resolver/blob/master/README.md)

#### Create an ObjectListenerResolver instance ####
This section will be written later.

### Create an adapter for the cache ###

### Configuration reference ###

### Mapping reference ###


### Configuration reference ###
To know more about configuration, see [configuration reference documentation](https://github.com/kassko/data-mapper/blob/master/Resources/doc/configuration.md)


For more details about mapping you can read the mapping reference documentations:

#### Inner yaml format ####
[see inner Yaml mapping reference documentation](https://github.com/kassko/data-mapper/blob/master/Resources/doc/inner_yaml_mapping.md).

#### Yaml file format ####
[see yaml mapping reference documentation](https://github.com/kassko/data-mapper/blob/master/Resources/doc/yaml_file_mapping.md).

#### Inner php ####
[see inner php mapping reference documentation](https://github.com/kassko/data-mapper/blob/master/Resources/doc/inner_php_mapping.md).

#### Php file format ####
[see Php mapping reference documentation](https://github.com/kassko/data-mapper/blob/master/Resources/doc/php_file_mapping.md).



