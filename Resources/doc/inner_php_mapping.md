### Inner Php mapping ###

Php mapping is Php provided by the domain object itself:
```php
class Keyboard
{
    public static function loadMapping()
    {
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
    }
}

//Fields sealDate and noSealDate don't appear in the field section because we don't want the mapper manage them.

```

"loadMapping" is the default provider method name but it can be changed:

Api configuration usage:
```php
$configuration->setDefaultClassMetadataProviderMethod('someMappingLoaderMethod');//<= for all domain objects

//or

$configuration->addClassMetadataProviderMethod('Kassko\Sample\Watch', 'myLoadMapping');//<= only for Watch objects
```

[see Php mapping reference documentation](https://github.com/kassko/data-mapper/blob/master/Resources/doc/php_file_mapping.md) for more details on Php mapping.