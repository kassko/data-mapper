# DataSourceRef

References a data source by ID for lazy loading properties.

## Usage

```php
use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\Attribute\MultiPropDataSource;
use Kassko\DataMapper\Attribute\DataSourceRef;

#[DataSourcesStore([
    new MultiPropDataSource(
        id: 'personData',
        class: PersonRepository::class,
        method: 'findById',
        args: ['#id']
    ),
])]
class Person
{
    private int $id;
    
    #[DataSourceRef(id: 'personData')]
    private ?string $firstName = null;
    
    #[DataSourceRef(id: 'personData')]
    private ?string $lastName = null;
}
```

## Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | `?string` | Conditional | Single data source ID |
| `chain` | `?array` | Conditional | Array of IDs for fallback chain |
| `providers` | `?array` | Conditional | Array of IDs for aggregation |
| `exceptionOnNoValidDataSource` | `?string` | No | Exception class for chain fallback |

**Note:** Only one of `id`, `chain`, or `providers` should be specified.

## Modes

### Simple Reference

```php
#[DataSourceRef(id: 'personData')]
private ?string $name = null;
```

### Fallback Chain

Try data sources in order until one succeeds:

```php
#[DataSourceRef(
    chain: ['sourceA', 'sourceB', 'sourceC'],
    exceptionOnNoValidDataSource: NoValidDataSourceException::class
)]
private ?string $data = null;
```

### Aggregation

Merge results from multiple sources:

```php
#[DataSourceRef(providers: ['providerA', 'providerB', 'providerC'])]
private ?array $config = null;
```

## See Also

- [DataSource](DataSource.md)
- [SinglePropDataSource](SinglePropDataSource.md)
- [MultiPropDataSource](MultiPropDataSource.md)
- [DataSourcesStore](DataSourcesStore.md)
