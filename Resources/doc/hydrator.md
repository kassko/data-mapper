Use the hydrator
==========

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

Use the hydrator to hydrate your object:

To do hydration and extraction operations, you get a DataMapper instance and you hydrate your object with its hydrator:
```php
$data = [
    'brand' => 'some brand',
    'COLOR' => 'blue',
];

$dataMapper = (new Kassko\DataMapper\DataMapperBuilder)->instance();

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

Inversely, use the hydrator to extract values from your object:
```php
$dataMapper = (new Kassko\DataMapper\DataMapperBuilder)->instance();

$object = (new Kassko\Sample\Watch)->setBrand('some brand')->setColor('color');
$rawRecord = $dataMapper->hydrator('Kassko\Sample\Watch')->extract($object);
var_dump($rawRecord);
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