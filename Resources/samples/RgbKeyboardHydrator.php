<?php

namespace Solfa\Bundle\ScalesBundle\Scales;

class RgbKeyboardHydrator
{
    public function hydrate($data, RgbKeyboardCustomHyd $rgbKeyboard)
    {
        //$rgbKeyboard->setBrand('YES');
        $rgbKeyboard->setBrand($data['brand']);
    }

    public function extract(RgbKeyboardCustomHyd $rgbKeyboard)
    {
        $data = [];
        $data['brand'] = $rgbKeyboard->getBrand();
        return $data;
    }
}