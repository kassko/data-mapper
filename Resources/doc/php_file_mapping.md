### Php file mapping ###

We return a Php array with the mapping.

```php
return [
    'interceptors' => [
        'postExtract' => 'onAfterExtract',
        'postHydrate' => 'onAfterHydrate',
    ],
    'fields' => [
        'brand' => [
            'readConverter' => 'readBrand',
            'writeConverter' => 'writeBrand',
        ],
        'color', //this field hasn't got specific configuration but we want the mapper manage it
        'createdDate' => [
            'name' => 'created_date',
            'type' => 'date',
            'readDateConverter' => 'Y-m-d H:i:s',
            'writeDateConverter' => 'Y-m-d H:i:s',
        ],
        'waterProof' => [
            'readConverter' => 'hydrateBool',
            'writeConverter' => 'extractBool',
        ],
        'stopWatch' => [
            'readConverter' => 'hydrateBoolFromSymbol',
            'writeConverter' => 'extractBoolToSymbol',
            'mappingExtensionClass' => 'WatchCallbacks',
        ],
        'customizable' => [
            'readConverter': 'hydrateBool',
            'writeConverter': 'extractBool',
            'getter': 'canBeCustomized',
        ],
    ],
];

//Fields sealDate and noSealDate don't appear in the field section because we don't want the mapper manage them.

```

Api configuration usage:
```php
$configuration->addClassMetadataResource('Kassko\Sample\Watch', 'some_php_file_path.php');
$configuration->addClassMetadataResourceType('Kassko\Sample\Watch', 'php_file');

//or

$configuration->setDefaultClassMetadataResourceDir('some_php_file_dir');
$configuration->addClassMetadataResource('Kassko\Sample\Watch', 'some_php_file_name.php');
$configuration->addClassMetadataResourceType('Kassko\Sample\Watch', 'php_file');
```

Here is an example with value objects:
```php
return [
    'fields' => [
        'brand', 'colorEn', 'colorFr', 'colorEs'
    ],
    'valueObjects' => [
        'colorEn' => [
            'class' => 'Kassko\Sample\Color',
            'mappingResourceType' => 'yaml_file',
            'mappingResourceName' => 'colorEn.yml',
        ],
        'colorFr' => [
            'class' => 'Kassko\Sample\Color',
            'mappingResourceType' => 'yaml_file',
            'mappingResourceName' => 'colorFr.yml',
        ],
        'colorEs' => [
            'class' => 'Kassko\Sample\Color',
            'mappingResourceType' => 'yaml_file',
            'mappingResourceName' => 'colorEs.yml',
        ],
    ],
];
```
