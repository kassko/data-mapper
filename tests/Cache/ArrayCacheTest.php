<?php
namespace Kassko\DataMapperTest\Cache;

use Kassko\DataMapper\Cache;

/**
 * Class ArrayCache
 * @package Kassko\DataMapperTest\Cache
 * @author Alexey Rusnak
 */
class ArrayCache extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Cache\ArrayCache
     */
    protected $cache;

    /**
     * @var array
     */
    protected $predefinedState = array(
        'stringKey' => 'stringKeyValue',
        1           => 'integerKeyValue'
    );

    /**
     * @return void
     */
    public function setUp()
    {
        $this->cache = new Cache\ArrayCache();

        foreach ($this->predefinedState as $id => $value) {
            $this->cache->save($id, $value);
        }
    }

    /**
     * @test
     */
    public function instanceOfCacheInterface()
    {
        $this->assertInstanceOf('Kassko\DataMapper\Cache\CacheInterface', $this->cache);
    }

    /**
     * @test
     * @dataProvider fetchDataProvider
     * @param mixed $id
     * @param mixed $expectedResult
     */
    public function fetchValidateReturnValue($id, $expectedResult)
    {
        $result = $this->cache->fetch($id);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * @test
     * @dataProvider containsDataProvider
     * @param mixed $id
     * @param mixed $expectedResult
     */
    public function containsValidateReturnValue($id, $expectedResult)
    {
        $result = $this->cache->contains($id);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * @test
     */
    public function saveValidateReturnValue()
    {
        $result = $this->cache->save('newId', 'test data for newId');
        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function deleteValidateReturnValue()
    {
        $result = $this->cache->delete('stringKey');
        $this->assertTrue($result);
    }

    /**
     * @test
     * @dataProvider saveDataProvider
     * @param mixed $id
     * @param mixed $data
     */
    public function saveValidateData($id, $data)
    {
        $this->cache->save($id, $data);

        $this->assertSame($data, $this->cache->fetch($id));
    }

    /**
     * @test
     * @dataProvider deleteDataProvider
     * @param mixed $id
     */
    public function deleteValidateData($id)
    {
        $this->cache->delete($id);

        $this->assertFalse($this->cache->contains($id));
    }

    /**
     * @return array
     */
    public function fetchDataProvider()
    {
        return array(
            array('stringKey', 'stringKeyValue'),
            array(1, 'integerKeyValue'),
            array('1', 'integerKeyValue'),
            array('nonExistentId', false),
            array(123456789, false)
        );
    }

    /**
     * @return array
     */
    public function containsDataProvider()
    {
        return array(
            array('stringKey', true),
            array(1, true),
            array('1', true),
            array('nonExistentId', false),
            array(123456789, false)
        );
    }

    /**
     * @return array
     */
    public function saveDataProvider()
    {
        return array(
            array('stringKey', 'stringKeyValueAfterSave'),
            array(1, 'integerKeyValueAfterSave'),
            array('1', 'integerKeyValueAfterSave'),
            array('nonExistentId', false),
            array('nonExistentId', false),
            array(123456789, false)
        );
    }

    /**
     * @return array
     */
    public function deleteDataProvider()
    {
        return array(
            array('stringKey'),
            array(1),
            array('1'),
            array('nonExistentId'),
            array(123456789)
        );
    }
}