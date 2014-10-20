* You can work with associations.

* ToOneAssociation

```php
use Kassko\DataAccess\Annotation as OM;

class Keyboard
{
    /**
     * @OM\Column
     * @OM\Id
     */
    private $id;

    /**
     * @OM\Column
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
     * @OM\Column
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
     * @OM\Column
     * @OM\Id
     */
    private $id;

    /**
     * @OM\Column
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

If the repository class wherin we wants to fetch is not that of the entity, we can override it:

```php
use Kassko\DataAccess\Annotation as OM;

class Keyboard
{
    /**
     * @OM\ToOne(entityClass="Manufacturer", repositoryClass="UnconventionnalManager" findMethod="find")
     * @OM\Column
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
use Doctrine\Common\Collections\ArrayCollection;

class Keyboard
{
    /**
     * @OM\Column
     * @OM\Id
     */
    private $id;

    /**
     * @OM\Column
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
     * @OM\Column
     * @OM\ToMany(entityClass="Shop", findMethod="findByKeyboard")
     */
    private $shops;

    public function __construct()
    {
        $this->shops = new ArrayCollection();
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
        //Some stuff to find shops witch sale the keyboard from identity "$id".
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
     * @OM\Column
     */
    private $id;

    /**
     * @OM\Column
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
use Doctrine\Common\Collections\ArrayCollection;

class Keyboard
{
    /**
     * @OM\ToMany(name="insertShop", entityClass="Shop", findMethod="find")
     * @OM\Column
     */
    private $shops;

    public function __construct()
    {
        $this->shops = new ArrayCollection();
    }

    public function insertShop(Shop $shop)
    {
        $this->shops[] = $shops;
    }
}
```

Note that in an entity, if no property is specified as the identifier, the default identifier is a property named "$id". And if no property "$id" exists, an exception is thrown when the system attempt to know the entity identifier.

For performance reasons, we can load the association "$shop" only when we use it. For more details see the [lazy loading reference documentation](https://github.com/kassko/data-access/blob/alpha/doc/lazy_loading.md).