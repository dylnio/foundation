<?php

namespace Dyln\Util;

class BooleanUtil
{
    public static function getBool($value)
    {
        if ($value === true) {
            return true;
        }
        if ($value === false) {
            return false;
        }
        if (in_array(strtolower($value), ['1', 1, 'yes', 'true', 'ok', '+', 'checked', 'on'])) {
            return true;
        }

        return false;
    }
}
