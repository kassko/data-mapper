Improve persistence ignorance
===========

DataMapper implements the DataMapper pattern which requires to separate domain objects and the persistence logic. But above you have used converters and put their callbacks in the domain object. You also append use statement which imports classes related to persistence. So your object is not really agnostic of persistence.

You can put your converter' callbacks in a dedicated class:
```php
namespace Kassko\Sample;

use Kassko\DataMapper\Hydrator\HydrationContextInterface;
use Kassko\DataMapper\Hydrator\Value;

class WatchMappingExtension
{
    private static $brandCodeToLabelMap = [1 => 'Brand A', 2 => 'Brand B'];
    private static $brandLabelToCodeMap = ['Brand A' => 1, 'Brand B' => 2];

    public static function readBrand(Value $value, HydrationContextInterface $context)
    {
        if (isset(self::$brandCodeToLabelMap[$value->value])) {
            $value->value = self::$brandCodeToLabelMap[$value->value];
        }
    }

    public static function writeBrand(Value $value, HydrationContextInterface $context)
    {
        if (isset(self::$brandLabelToCodeMap[$value->value])) {
            $value->value = self::$brandLabelToCodeMap[$value->value];
        }
    }

    public static function hydrateBool(Value $value, HydrationContextInterface $context)
    {
        $value->value = $value->value == '1';
    }

    public static function extractBool(Value $value, HydrationContextInterface $context)
    {
        $value->value = $value->value ? '1' : '0';
    }

    public static function hydrateBoolFromSymbol(Value $value, HydrationContextInterface $context)
    {
        $value->value = $value->value == 'X';
    }

    public static function extractBoolToSymbol(Value $value, HydrationContextInterface $context)
    {
        $value->value = $value->value ? 'X' : ' ';
    }
}
```

#### Annotations format ####
```php
namespace Kassko\Sample;

use Kassko\DataMapper\Annotation as DM;
use \DateTime;

/**
 * @DM\Object(classMappingExtensionClass="Kassko\\Sample\\WatchMappingExtension")
 */
class Watch
{
    /**
     * @DM\Field(readConverter="readBrand", writeConverter="writeBrand")
     */
    private $brand;

    /**
     * @DM\Field(readConverter="hydrateBool", writeConverter="extractBool")
     */
    private $waterProof;

    /**
     * @DM\Field(readConverter="hydrateBoolFromSymbol", writeConverter="extractBoolToSymbol")
     */
    private $stopWatch;

    /**
     * @DM\Field(type="date", readDateConverter="Y-m-d H:i:s", writeDateConverter="Y-m-d H:i:s")
     */
    private $createdDate;

    public function getBrand() { return $this->brand; }
    public function setBrand($brand) { $this->brand = $brand; }
    public function isWaterProof() { return $this->waterProof; }
    public function setWaterProof($waterProof) { $this->waterProof = $waterProof; }
    public function hasStopWatch() { return $this->stopWatch; }
    public function setStopWatch($stopWatch) { $this->stopWatch = $stopWatch; }
    public function getCreatedDate() { return $this->createdDate; }
    public function setCreatedDate(DateTime $createdDate) { $this->createdDate = $createdDate; }
}
```

As you can see, you have removed all the code about the conversion and specified in your mapping the extension class which now contains this code.

You also can choose outer mapping formats ("yaml_file", "php_file") instead of inner ones ("annotations"). Annotations are not part of the executed code but they are still physically present in the domain object.

Your mapping in a Yaml file:
```yml
object:
    classMappingExtensionClass: "Kassko\\Sample\\WatchMappingExtension"
fields:
    brand:
        readConverter: readBrand
        writeConverter: writeBrand
    waterProof:
        readConverter: hydrateBool
        writeConverter: extractBool
    stopWatch:
        readConverter: hydrateBoolFromSymbol
        writeConverter: extractBoolToSymbol
    createdDate:
        name: created_date
        type: date
        readDateConverter: "Y-m-d H:i:s"
        writeDateConverter: "Y-m-d H:i:s"
```

And your domain object:
```php
namespace Kassko\Sample;

use \DateTime;

class Watch
{
    private $brand;
    private $waterProof;
    private $stopWatch;
    private $createdDate;

    public function getBrand() { return $this->brand; }
    public function setBrand($brand) { $this->brand = $brand; }
    public function isWaterProof() { return $this->waterProof; }
    public function setWaterProof($waterProof) { $this->waterProof = $waterProof; }
    public function hasStopWatch() { return $this->stopWatch; }
    public function setStopWatch($stopWatch) { $this->stopWatch = $stopWatch; }
    public function getCreatedDate() { return $this->createdDate; }
    public function setCreatedDate(DateTime $createdDate) { $this->createdDate = $createdDate; }
}
```

Is the Watch object persisted ? We really don't know ! However, you could reply that's very practical to work with annotations because we can see both the domain object and its mapping rules. That's right ! Both have advantages and drawbacks. The choice is yours !