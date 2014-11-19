* You can work with associations.

* ToOneAssociation

```php
use Kassko\DataAccess\Annotation as OM;

class Keyboard
{
    /**
     * @OM\Field
     * @OM\Id
     */
    private $id;

    /**
     * @OM\Field
     */
    private $color;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @OM\ToOne(entityClass="Manufacturer", findMethod="find")
     * @OM\Field(name="manufacturer_id")
     */
    private $manufacturer;

    public function getColor()
    {
        return $this->color;
    }

    public function setColor($color)
    {
        $this->color = $color;
    }

    public function getManufacturer()
    {
        return $this->manufacturer;
    }

    public function setManufacturer(array $manufacturer)
    {
        $this->manufacturer = $manufacturer;
    }
}
```

As you can guess, the "find" method is that of the repository of the entity "Manufacturer" meanning "ManufacturerManager::find()".

```php
/**
 * @OM\Entity(repositoryClass="ManufacturerManager")
 */
class Manufacturer
{
    /**
     * @OM\Field
     * @OM\Id
     */
    private $id;

    /**
     * @OM\Field
     */
    private $name;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }
}
```

```php
class ManufacturerManager
{
    /**
     * Return a manufacturer object from its identity
     * @param integer $id The identity of object to find
     *
     * @return Manufacturer
     */
    public function find($id)
    {
        //Some stuff to find the manufacturer.
    }
}
```

Usage:
```php
use Kassko\DataAccess\DataAccessProvider;

$data = [
    'id' => 1,
    'color' => 'blue',
    'manufacturer_id' => 1
];

$provider = new DataAccessProvider;
$resultBuilderFactory = $provider->getResultBuilderFactory();
$resultBuilder = $resultBuilderFactory->createResultBuilder('Keyboard', $data);
var_dump($resultBuilder->getSingleResult());
```

Display result:
```php
object(Keyboard)#283 (8) {
    ["id":"Keyboard":private]=> int(1)
    ["color":"Keyboard":private]=> string(4) "blue"
    ["manufacturer":"Manufacturer":private]=>
    object(Manufacturer)#320 (3) {
        ["id":"Manufacturer":private]=> int(1)
        ["name":"Manufacturer":private]=> string(10) "Some brand"
    }
}
```

If the repository class wherin we wants to fetch is not that of the entity, we can override it:

```php
use Kassko\DataAccess\Annotation as OM;

class Keyboard
{
    /**
     * @OM\ToOne(entityClass="Manufacturer", repositoryClass="UnconventionnalManager" findMethod="find")
     * @OM\Field
     */
    private $manufacturer;
}
```
The find method will be "UnconventionnalManager::find()" instead of "ManufacturerManager::find()".

For performance reasons, we can load the association "$manufacturer" only when we use it. For more details see the [lazy loading reference documentation](https://github.com/kassko/data-access/blob/alpha/doc/lazy_loading.md).

* ToManyAssociation

An association "to many" is used similarly.

```php
use Kassko\DataAccess\Annotation as OM;

class Keyboard
{
    /**
     * @OM\Field
     * @OM\Id
     */
    private $id;

    /**
     * @OM\Field
     */
    private $color;

    /**
     * @OM\Field
     * @OM\ToMany(entityClass="Shop", findMethod="findByKeyboard")
     */
    private $shops;

    public function __construct()
    {
        $this->shops = [];
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getColor()
    {
        return $this->color;
    }

    public function setColor($color)
    {
        $this->color = $color;
    }

    public function getShops()
    {
        return $this->shops;
    }

    public function addShop(Shop $shop)
    {
        $this->shops[] = $shops;
    }
}
```

```php
class ShopManager
{
    /**
     * Return shops witch sale a keyboard from a given identity
     * @param integer $id Id of the keyboard
     *
     * @return Shops[]
     */
    public function findByKeyboard($id)
    {
        //Some stuff to find shops witch sale the keyboard with identity "$id".
    }
}
```
```php
/**
 * @OM\Entity(repositoryClass="ShopManager")
 */
class Shop
{
    /**
     * @OM\Id
     * @OM\Field
     */
    private $id;

    /**
     * @OM\Field
     */
    private $name;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }
}
```

"Shop" is the entity class name, then "Shop" is the association name, then in "Keyboard" entity the "adder" is "addShop()".
The association name is the entity class name not full qualified.

If the "Shop" FQCN (full qualified class name) is "Kassko\Sample\Shop", the association name will be "Shop" and the adder will be "addShop()".

You can override this association name:
```php
use Kassko\DataAccess\Annotation as OM;

class Keyboard
{
    /**
     * @OM\ToMany(name="insertShop", entityClass="Shop", findMethod="find")
     * @OM\Field
     */
    private $shops;

    public function __construct()
    {
        $this->shops = [];
    }

    public function insertShop(Shop $shop)
    {
        $this->shops[] = $shops;
    }
}
```

Usage:
```php
$data = [
    'id' => 1,
    'color' => 'blue'
];

//=====> Here some stuff to create $resultBuilderFactory <=====
$resultBuilder = $resultBuilderFactory->createResultBuilder($data, 'Keyboard');
var_dump($resultBuilder->getSingleResult());
```

Possible display result:
```php
object(Keyboard)#283 (8) {
    ["id":"Keyboard":private]=> int(1)
    ["color":"Keyboard":private]=> string(4) "blue"
    array(3) {
        [0] =>
        ["shops":"Shop":private]=>
            object(Shop)#320 (3) {
                ["id":"Shop":private]=> int(1)
                ["name":"Shop":private]=> string(14) "Shop A"
            }
        }
        [1] =>
        ["shops":"Shop":private]=>
            object(Shop)#320 (3) {
                ["id":"Shop":private]=> int(2)
                ["name":"Shop":private]=> string(14) "Shop B"
            }
        }
    }
}
```

Note that in an entity, if no property is specified as the identifier, the default identifier is a property named "$id". And if no property "$id" exists, an exception is thrown when the system attempt to know the entity identifier.

For performance reasons, we can load the association "$shop" only when we use it. For more details see the [lazy loading reference documentation](https://github.com/kassko/data-access/blob/alpha/doc/lazy_loading.md).