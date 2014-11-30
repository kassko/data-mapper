### Yaml file mapping ###

#### First example ####
```yaml
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
```