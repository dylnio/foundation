<?php

namespace Dyln;

use Dyln\Util\ArrayUtil;

if (!function_exists('Dyln\getin')) {
    function getin($array, $keys, $default = null)
    {
        return ArrayUtil::getIn($array, $keys, $default);
    }
}