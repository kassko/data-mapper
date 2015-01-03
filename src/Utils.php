<?php

namespace Kassko\DataMapper;

class Utils
{
    public static function getUnpackedSettings(array $settings)
    {
        $unpackedSettings = [];
        self::unpackSettings($settings, $unpackedSettings);

        return $unpackedSettings;
    }

    public static function unpackSettings(array $settings, array &$unpackedSettings)
    {
        foreach ($settings as $namespace => $setting) {

            if (is_array($setting)) {

                $unpackedSetting = [];
                self::unpackSettings($setting, $unpackedSetting);
            } else {

                $unpackedSetting = $setting;
            }

            if (null === strstr($namespace, '.')) {

                $nsToUnpack[$namespace] = false;
            }  else {

                $namespaceParts = explode('.', $namespace);

                list(, $namespacePart) = each($namespaceParts);
                $unpackedSettings[$namespacePart] = null;
                $unpackedSettingsRef = & $unpackedSettings[$namespacePart];

                //Since there is a '.', we always loop at least one time.
                while (list(, $namespacePart) = each($namespaceParts)) {

                    $unpackedSettingsRef[$namespacePart] = null;
                    $unpackedSettingsRef = & $unpackedSettingsRef[$namespacePart];
                }
                $unpackedSettingsRef = $unpackedSetting;
            }
        }
    }
}
