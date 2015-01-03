<?php

use Kassko\DataMapper\Utils;

class UtilsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider unpackSettingsProvider
     */
    public function testUnpackSettings(array $settings, array $expectedUnpackedSettings)
    {
        $unpackedSettings = [];
        Utils::unpackSettings($settings, $unpackedSettings);

        $this->assertEquals($expectedUnpackedSettings, $unpackedSettings);
    }

    public function unpackSettingsProvider()
    {
        return [
            [
                ['key' => 'value'],
                ['key' => 'value'],
            ],
            [
                ['key_a.key_b' => 'value'],
                ['key_a' => ['key_b' => 'value'] ],
            ],
            [
                ['key_a' => ['key_b.key_c' => 'value']],
                ['key_a' => ['key_b' => ['key_c' => 'value']]],
            ],
            [
                ['key_a.key_b' => ['key_c.key_d' => 'value']],
                ['key_a' => [ 'key_b' => ['key_c' => ['key_d' => 'value']]]],
            ],
            [
                ['key_a' => [['key_b.key_c' => 'value'], ['key_b.key_d' => 'value']]],
                ['key_a' => [['key_b' => ['key_c' => 'value']], ['key_b' => ['key_d' => 'value']]]],
            ],
        ];
    }
}