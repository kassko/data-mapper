Field exclusion pilicy
===================

By default, all fields are managed (hydrated and extracted). You can ignore some fields with the annotation Exclude.

```php
namespace Kassko\Sample;

use Kassko\DataMapper\Annotation as DM;

class Watch
{
    /**
     * DM\Exclude
     */
    private $brand;//Is not managed because of Exclude annotation.

    /**
     * DM\Fields(name="COLOR")
     */
    private $color;//Is managed because of fieldExclusionPolicy tuned by default to "include_all".

    public function getBrand() { return $this->brand; }
    public function setBrand($brand) { $this->brand = $brand; }
    public function getColor() { return $this->color; }
    public function setColor($color) { $this->color = $color; }
}
```

You can switch the behaviour such as it ignores all fields by default.

```php
namespace Kassko\Sample;

use Kassko\DataMapper\Annotation as DM;

/**
 * DM\Object(fieldExclusionPolicy="exclude_all")
 */
class Watch
{
    private $brand;//Is not managed because of fieldExclusionPolicy tuned to "exclude_all".

    /**
     * DM\Include
     * DM\Fields(name="COLOR")
     */
    private $color;//Is managed because of Include annotation.

    public function getBrand() { return $this->brand; }
    public function setBrand($brand) { $this->brand = $brand; }
    public function getColor() { return $this->color; }
    public function setColor($color) { $this->color = $color; }
}
``` 

If not specify, fieldExclusionPolicy is tuned on "exclude_all".