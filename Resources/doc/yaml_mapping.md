### Yaml mapping ###

Yaml mapping is Yaml provided by the domain object itself:
```php
class Keyboard
{
    public static function loadMapping()
    {
        return <<<EOF
object:
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
EOF;
    }
}
```

"loadMapping" is the default provider method name but it can be changed:

```php
$configuration->setDefaultClassMetadataProviderMethod('someMappingLoaderMethod');//<= for all domain objects

//or

$configuration->addClassMetadataProviderMethod('Kassko\Sample\Watch', 'someMappingLoaderMethod');//<= only for Watch objects
```

[see Yaml mapping reference documentation](https://github.com/kassko/data-access/blob/master/Resources/doc/yaml_file_mapping.md) for more details on Yaml mapping.