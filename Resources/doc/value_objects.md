Value objects
===============

### Map composite objects ###

```php
namespace Kassko\Sample;

use Kassko\DataMapper\Annotation as DM;

class Customer
{
    /**
     * @DM\Field
     * @DM\Id
     */
    private $id;

    /**
     * @DM\Field
     * @DM\ValueObject(class="Kassko\Sample\Address", mappingResourceType="yaml", mappingResourceName="billing_address.yml")
     */
    private $billingAddress;//$billingAddress is a value object.

    /**
     * @DM\Field
     * @DM\ValueObject(class="Kassko\Sample\Address", mappingResourceType="yaml", mappingResourceName="shipping_address.yml")
     */
    private $shippingAddress;//$shippingAddress is a value object too.
}
```

```php
namespace Kassko\Sample;

class Address
{
    private $street;
    private $town;
    private $postalCode;
    private $country;
}
```

```yaml
# billing_address.yml

fields:
    street:
        name: billing_street
    town:
        name: billing_town
    postalCode:
        name: billing_postal_code
    country:
        name: billing_country
```

```yaml
# shipping_address.yml

fields:
    street:
        name: shipping_street
    town:
        name: shipping_town
    postalCode:
        name: shipping_postal_code
    country:
        name: shipping_country
```

```php

$data = [
    'id' => 1,
    'billing_street' => '12 smarties street',
    'billing_town' => 'Nuts',
    'billing_postal_code' => '654321'
    'billing_country' => 'England',
    'shipping_street' => '23 smarties street',
    'shipping_town' => 'Mars',
    'shipping_postal_code' => '987654'
    'shipping_country' => 'England',
];

$dataMapper->resultBuilder('Kassko\Sample\Customer', $data)->single();
```
Note that you can have value objects which contains value objects and so on. And each value object can use it's own mapping configuration format.