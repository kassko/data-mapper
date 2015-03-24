Isser, haser and custom getters/setters
============

DataMapper automatically recognize getter (or isser or haser) and setter of a field.

#### Annotations format ####
```php
namespace Kassko\Sample;

use Kassko\DataMapper\Annotation as DM;

class Watch
{
    /**
     * @DM\Field
     */
    private $brand;

    /**
     * @DM\Field
     */
    private $waterProof;

    /**
     * @DM\Field
     */
    private $stopWatch;

    public function getBrand() { return $this->brand; }
    public function setBrand($brand) { $this->brand = $brand; }
    public function isWaterProof() { return $this->waterProof; }
    public function setWaterProof($waterProof) { $this->waterProof = $waterProof; }
    public function hasStopWatch() { return $this->stopWatch; }
    public function setStopWatch($stopWatch) { $this->stopWatch = $stopWatch; }
}
```

To retrieve the corresponding getter, DataMapper look in order at:
* a getter
* an isser
* a haser

You also can specify corresponding getters/setters:

#### Annotations format ####
```php
namespace Kassko\Sample;

use Kassko\DataMapper\Annotation as DM;

class Watch
{
    /**
     * @DM\Field(prefix="is")
     */
    private $waterProof;

    /**
     * @DM\Field
     * @DM\Getter(prefix="has")
     */
    private $alarm;

    /**
     * @DM\Field
     * @DM\Getter(prefix="are")
     */
    private $handsYellow;

    /**
     * @DM\Field(name="hasStopWatch")
     */
    private $stopWatch;

    /**
     * @DM\Field
     * @DM\Getter(name="canColorChange")
     * @DM\Setter(name="colorCanChange")
     */
    private $variableColor;

    public function isWaterProof() { return $this->waterProof; }
    public function setWaterProof($waterProof) { $this->waterProof = $waterProof; }
    public function hasAlarm() { return $this->alarm; }
    public function setAlarm($stopWatch) { $this->alarm = $alarm; }
    public function areHandsYellow() { return $this->handsYellow; }
    public function setHandsyellow($handsYellow) { $this->handsYellow = $handsYellow; }
    public function hasStopWatch() { return $this->stopWatch; }
    public function setStopWatch($stopWatch) { $this->stopWatch = $stopWatch; }
    public function canColorChange() { return $this->variableColor; }
    public function colorCanChange($colorCanChange) { $this->variableColor = $colorCanChange; }
}
```