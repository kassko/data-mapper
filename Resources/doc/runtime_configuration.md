Runtime configuration
===============

Use several mapping configuration for the same domain object and switch mapping configuration on runtime for a domain object

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