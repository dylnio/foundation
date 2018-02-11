<?php

namespace Dyln\Util;

class IntUtil
{
    public static function toint($value)
    {
        return intval(strval($value));
    }
}
