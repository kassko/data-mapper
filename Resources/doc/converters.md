 Converters
================

#### Annotations format ####
```php
namespace Kassko\Sample;

use Kassko\DataMapper\Annotation as DM;
use Kassko\DataMapper\Hydrator\HydrationContextInterface;
use Kassko\DataMapper\Hydrator\Value;
use \DateTime;

class Watch
{
    private static $brandCodeToLabelMap = [1 => 'Brand A', 2 => 'Brand B'];
    private static $brandLabelToCodeMap = ['Brand A' => 1, 'Brand B' => 2];

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

readDateConverter contains the format of the string to transform into Date object. Internally, DataMapper uses the Php function DateTime::createFromFormat() to create a Date object from a raw string.

writeDateConverter contains the string format in which you want to serialize your Date object. Internally, DataMapper uses the Php function DateTime::createFromFormat() to serialise the Date object in a string.

Given this code:
```php
$data = [
    'brand' => '1',
    'waterProof' => '1',
    'stopWatch' => 'X',
    'created_date' => '2014-09-14 12:36:52',
];

$dataMapper = (new Kassko\DataMapper\DataMapperBuilder)->instance();
$object = new Kassko\Sample\Watch;
$dataMapper->hydrator('Kassko\Sample\Watch')->hydrate($data, $object);
var_dump($object);
```

Your output will be:
```php
object(Watch)#283 (8) {
    ["brand":"Watch":private]=> string(10) "Brand A"
    ["waterProof":"Watch":private]=> bool(true)
    ["stopWatch":"Watch":private]=> bool(true)
    ["createdDate":"Watch":private]=>
        object(DateTime)#320 (3) { ["date"]=> string(19) "2014-09-14 12:36:52" ["timezone_type"]=> int(3) ["timezone"]=> string(13) "Europe/Berlin" }
}
```

If the created_date had a bad format, an exception would have been thrown.
For example, the format given above in the read date converter is 'Y-m-d H:i:s', so a create_date like '2014 09 14 12h36m52s' is not correct.