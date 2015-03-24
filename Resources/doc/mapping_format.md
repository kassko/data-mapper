Mapping configuration format
=====================

Several mapping configuration format are availables. You can choose the one you prefer among the following:

* `annotations`
* `yaml`
* `php`

* `inner_yaml`
* `inner_php`


```php
namespace Kassko\Sample;

use Kassko\DataMapper\Annotation as DM;

class Watch
{
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

#### Inner php format ####

```php
namespace Kassko\Sample;

use Kassko\DataMapper\Annotation as DM;

class Watch
{
    private $brand;

    /**
     * @DM\Field(name="COLOR")
     */
    private $color;

    public function getBrand() { return $this->brand; }
    public function setBrand($brand) { $this->brand = $brand; }
    public function getColor() { return $this->color; }
    public function setColor($color) { $this->color = $color; }

    public function provideMapping()
    {
        return [
            'fields' => [
                'color' => ['name' => 'COLOR'],
            ]
        ];
    }
}
```

#### Yaml php format ####

```php
namespace Kassko\Sample;

use Kassko\DataMapper\Annotation as DM;

class Watch
{
    private $brand;

    /**
     * @DM\Field(name="COLOR")
     */
    private $color;

    public function getBrand() { return $this->brand; }
    public function setBrand($brand) { $this->brand = $brand; }
    public function getColor() { return $this->color; }
    public function setColor($color) { $this->color = $color; }

    public function provideMapping()
    {
        return <<<EOF
fields:
    color:
        name: "COLOR"
EOF;
    }
}
```
Later you'll see that you can provide your own format.