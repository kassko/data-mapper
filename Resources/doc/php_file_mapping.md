### Php file mapping ###

We return a Php array with the mapping.

```php
return [
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
            'getter' => ['prefix' => 'is'], //Type of getter. "get" for getter (default value), "is" for isser, "has" for hasser.
        ],
        'stopWatch' => [
            'readConverter' => 'hydrateBoolFromSymbol',
            'writeConverter' => 'extractBoolToSymbol',
            'mappingExtensionClass' => 'WatchCallbacks',
        ],
        'customizable' => [
            'readConverter': 'hydrateBool',
            'writeConverter': 'extractBool',
            'getter': 'canBeCustomized',//Getter name for property customizable.
        ],
    ],
    'processors' => [
        'preHydrate' => [
            ['class'=> 'SomeClass', 'method' => 'onBeforeHydrate'],
        ],
        'postHydrate' => [
            ['class' => 'SomeClass', 'method' => 'onAfterHydrate', 'args' => ['foo', 123]],
        ]
        'preExtract' => [
            ['class' => 'SomeClassA', 'method' => 'onBeforeExtract'],
            ['class' => 'SomeClassB', 'method' => 'onBeforeExtract', 'args' => ['foo', 123]],
        ],
        'postExtract' => [
            ['class' => 'SomeClass', 'method' => 'onAfterExtractA'],
            ['class' => 'SomeClass', 'method' => 'onAfterExtractB', 'args' => ['foo', 123]], 
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
