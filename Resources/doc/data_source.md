Data source
==============

```php
namespace Kassko\Sample;

use Kassko\DataMapper\Annotation as DM;

class Information
{
    /**
     * @DM\DataSource(class="Kassko\Sample\ShopDataSource", method="getBestShop")
     * @DM\Field(class='Kassko\Sample\Shop')
     */
    private $bestShop;

    /**
     * @DM\DataSource(class="Kassko\Sample\ShopDataSource", method="getNbShops")
     */
    private $nbShops;

    public function getBestShop() { return $this->bestShop; }
    public function setBestShop(Shop $shop) { $this->bestShop = $bestShop; }
}
```

```php
namespace Kassko\Sample;

use Kassko\DataMapper\Annotation as DM;

class Shop
{
    /**
     * @DM\Field
     */
    private $name;

    /**
     * @DM\Field
     */
    private $address;
}
```

```php
namespace Kassko\Sample;

class ShopDataSource
{
    public function getBestShop()
    {
        return [
        	'name' => 'The best',
        	'address' => 'Street of the bests',
        ];
    }

    public function getNbShops()
    {
    	return 25;
    }
}
```

We can load the properties "bestShop" and "keyboard" only when we use it.

```php
namespace Kassko\Sample;

use Kassko\DataMapper\Annotation as DM;

class Information
{
	use LazyLoadableTrait;

    /**
     * @DM\DataSource(class="Kassko\Sample\ShopDataSource", method="getBestShop", lazyLoading=true)
     * @DM\Field(class='Kassko\Sample\Shop')
     */
    private $bestShop;

    /**
     * @DM\DataSource(class="Kassko\Sample\ShopDataSource", method="getNbShops", lazyLoading=true)
     */
    private $nbShops;

    public function getBestShop()
    {
        $this->loadProperty('bestShop');//<= Load the best shop from the property name if not loaded.
        return $this->bestShop;
    }

    public function getNbShop()
    {
        $this->loadProperty('nbShops');//<= Load the best shop from the property name if not loaded.
        return $this->nbShops;
    }
}
```

You can supply several fields with the same data source method:

```php
namespace Kassko\Sample;

class ShopDataSource
{
    public function getShopsSurveyInfo()
    {
        return [
        	'bestShop' => [
	        	'name' => 'The best',
	        	'address' => 'Street of the bests',
    		],
    		'worstShop' => [
	        	'name' => 'The worst',
	        	'address' => 'Street of the worsts',
    		],
        ];
    }
}
```

```php
namespace Kassko\Sample;

use Kassko\DataMapper\Annotation as DM;

class Information
{
	use LazyLoadableTrait;

    /**
     * @DM\DataSource(class="Kassko\Sample\ShopDataSource", method="getShopsSurveyInfo", lazyLoading=true, supplySeveralFields=true)
     * @DM\Field(class='Kassko\Sample\Shop')
     */
    private $bestShop;

    /**
     * @DM\DataSource(class="Kassko\Sample\ShopDataSource", method="getShopsSurveyInfo", lazyLoading=true, supplySeveralFields=true)
     * @DM\Field(class='Kassko\Sample\Shop')
     */
    private $worstShop;

    public function getBestShop()
    {
        $this->loadProperty('bestShop');//<= Supply the best shop and the worst shop properties if not loaded.
        return $this->bestShop;
    }

    public function getWorstShop()
    {
        $this->loadProperty('worstShop');//<= Supply the best shop and the worst shop properties if not loaded.
        return $this->worstShop;
    }
}
```

