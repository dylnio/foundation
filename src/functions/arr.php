<?php

namespace Dyln;

use Dyln\Util\ArrayUtil;

if (!function_exists('Dyln\getin')) {
    function getin($array, $keys, $default = null)
    {
        return ArrayUtil::getIn($array, $keys, $default);
    }

    function has($array, $key)
    {
        return ArrayUtil::has($array, $key);
    }

    function set(&$arr, $path, $value, $separator = '.')
    {
        $keys = explode($separator, $path);

        foreach ($keys as $key) {
            $arr = &$arr[$key];
        }

        $arr = $value;
    }
}
