<?php

namespace Dyln;

use ReflectionClass;

class Enum
{
    public static function isValid($key = null)
    {
        if (!$key) {
            return false;
        }
        $consts = self::getAsArray();

        return in_array($key, $consts);
    }

    public static function getAsArray()
    {
        $refl = new ReflectionClass(get_called_class());

        return array_values($refl->getConstants());
    }

    public static function getAsAssocArray()
    {
        $refl = new ReflectionClass(get_called_class());

        return $refl->getConstants();
    }
}
