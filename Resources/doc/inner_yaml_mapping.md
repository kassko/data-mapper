### Inner Yaml mapping ###

Yaml mapping is Yaml provided by the domain object itself:
```php
class Keyboard
{
    public static function loadMapping()
    {
        return <<<EOF
fields:
    brand:
        readConverter: readBrand
        writeConverter: writeBrand
    color: ~ # this field hasn't got specific configuration but we want the mapper manage it
    createdDate:
        name: created_date
        type: date
        readDateConverter: "Y-m-d H:i:s"
        writeDateConverter: "Y-m-d H:i:s"
    waterProof:
        readConverter: hydrateBool
        writeConverter: extractBool
        getter:
            prefix: is # Type of getter. "get" for getter (default value), "is" for isser, "has" for hasser.
    stopWatch:
        readConverter: hydrateBoolFromSymbol
        writeConverter: extractBoolToSymbol
        mappingExtensionClass: WatchCallbacks
    customizable:
        readConverter: hydrateBool
        writeConverter: extractBool
        getter: canBeCustomized # Getter name for property customizable.
processors:
    preHydrate:
        - {class: SomeClass, method: onBeforeHydrate}
    postHydrate:
        - {class: SomeClass, method: onAfterHydrate, args: ['foo', 123]}
    preExtract:
        - {class: SomeClassA, method: onBeforeExtract}
        - {class: SomeClassB, method: onBeforeExtract, args: ['foo', 123]}
    postExtract:
        - {class: SomeClass, method: onAfterExtractA}
        - {class: SomeClass, method: onAfterExtractB, args: ['foo', 123]}
        
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

[see Yaml mapping reference documentation](https://github.com/kassko/data-mapper/blob/master/Resources/doc/yaml_file_mapping.md) for more details on Yaml mapping.