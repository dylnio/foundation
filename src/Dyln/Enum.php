<?php

namespace Dyln;

use ReflectionClass;

class Enum
{
    public static function isValid($key)
    {
        $consts = self::getAsArray();

        return in_array($key, $consts);
    }

    public static function getAsArray()
    {
        $refl = new ReflectionClass(get_called_class());

        return array_values($refl->getConstants());
    }
}
