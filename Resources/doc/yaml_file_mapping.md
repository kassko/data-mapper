### Yaml file mapping ###

#### First example ####
```yaml
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

# Fields sealDate and noSealDate don't appear in the field section because we don't want the mapper manage them
```

Api configuration usage:
```php
$configuration->addClassMetadataResource('Kassko\Sample\Watch', 'some_yaml_file_path.yml');
$configuration->addClassMetadataResourceType('Kassko\Sample\Watch', 'yaml_file');

//or

$configuration->setDefaultClassMetadataResourceDir('yaml_file_dir');
$configuration->addClassMetadataResource('Kassko\Sample\Watch', 'some_yaml_file_name.yml');
$configuration->addClassMetadataResourceType('Kassko\Sample\Watch', 'yaml_file');
```
