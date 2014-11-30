### Php mapping ###

Php mapping is Php provided by the domain object itself:
```php
class Keyboard
{
    public static function loadMapping()
    {
        return [
            'object' => [
                'interceptors' => [
                    'postExtract' => 'onAfterExtract',
                    'postHydrate' => 'onAfterHydrate',
                ],
            ],
            'fields' => [
                'brand' => [
                    'readStrategy' => 'readBrand',
                    'writeStrategy' => 'writeBrand',
                ],
                'color', //this field hasn't got specific configuration but we want the mapper manage it
                'createdDate' => [
                    'name' => 'created_date',
                    'type' => 'date',
                    'readDateFormat' => 'Y-m-d H:i:s',
                    'writeDateFormat' => 'Y-m-d H:i:s',
                ],
                'waterProof' => [
                    'readStrategy' => 'hydrateBool',
                    'writeStrategy' => 'extractBool',
                ],
                'stopWatch' => [
                    'readStrategy' => 'hydrateBoolFromSymbol',
                    'writeStrategy' => 'extractBoolToSymbol',
                    'mappingExtensionClass' => 'WatchCallbacks',
                ],
                'customizable' => [
                    'readStrategy': 'hydrateBool',
                    'writeStrategy': 'extractBool',
                    'getter': 'canBeCustomized',
                ],
            ],
        ];
    }
}

//Fields sealDate and noSealDate don't appear in the field section because we don't want the mapper manage them.

```

"loadMapping" is the default provider method name but it can be changed:

```php
$configuration = $objectManager->getConfiguration();
$configuration->setDefaultClassMetadataProviderMethod('myLoadMapping');//<= for all domain objects
//or
$configuration->addClassMetadataProviderMethod('Kassko\Sample\Keyboard', 'myLoadMapping');//<= only for Keyboard objects
```

[see Php mapping reference documentation](https://github.com/kassko/data-access/blob/master/Resources/doc/php_file_mapping.md) for more details on Php mapping.