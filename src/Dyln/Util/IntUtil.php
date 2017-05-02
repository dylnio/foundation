<?php

namespace Dyln\Util;

class IntUtil
{
    static public function toint($value)
    {
        return intval(strval($value));
    }
}