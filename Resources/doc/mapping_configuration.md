Configuration
=================

### Configure the DataMapper ###

The DataMapper has several settings that we haven't used.

For example, if you want to use another mapping format than the default one ('annotation'), you need to configure the DataMapper before its instanciation:

```php
use Kassko\DataMapper\DataMapperBuilder;

$dataMapper = (new DataMapperBuilder)
    ->settings(
        [
            'default_resource_type' => 'yaml_file',
            'default_resource_dir' => 'c:\mapping',
            'mapping.objects' =>
            [
                [
                    'class' => 'Kassko\Sample\Watch'
                    'resource_path' => 'c:\some_project\mapping\watch.yml'
                ],
                [
                    'class' => 'Kassko\Sample\Keyboard'
                    'resource_type' => 'annotations'
                ],
            ]
        ]
    )
    ->instance()
;
```

The code above means:
* the default mapping format is yaml_file
* by default, mapping yaml files are located in 'c:\mapping'
* except for the Watch class which overrides the yaml mapping file location to 'c:\some_project\mapping\watch.yml'
* the Keyboard class uses the annotations format

For more information, see the section named configuration reference.

### Ignore fields for mapping ###

Sometimes, you can have a field which has no correspondance with raw data and you want to set its value yourself. You can specify the DataMapper not to try to map this field.

#### Annotations format ####
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
     * @DM\Field(name="COLOR")
     */
    private $color;

    private $loadingDate;//This field has no field annotation, it will not be managed.

    public function getBrand() { return $this->brand; }
    public function setBrand($brand) { $this->brand = $brand; }
    public function getColor() { return $this->color; }
    public function setColor($color) { $this->color = $color; }
    public function setLoadingDate(\DateTime $loadingDate) { $this->loadingDate = $loadingDate; }
}
```

#### Yaml file format ####
```yaml
fields:
    brand: ~
    color:
        name: "COLOR"
# The field loadingDate do not appear in the fields section, it will not be managed.
```

#### Php file format ####
```php
return [
    'fields' => [
        'brand',
        'color' => ['name' => 'COLOR'],
];

//The field loadingDate do not appear in the fields section, it will not be managed.
```

But be careful, this feature has not been implemented to allow you to ignore some dependencies during mapping process. It's not a good practice to create persistent object with some dependencies (like a logger or a mailer). Your object shoud keep a POPO (Plain Old Php Object).