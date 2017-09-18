<?php

namespace Dyln;

use Dyln\Collection\Collection;
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

    function isassoc($arr)
    {
        return ArrayUtil::isAssoc($arr);
    }

    function makearr($string, $delimiter = ',')
    {
        if (is_array($string)) {
            return $string;
        }

        return Collection::create(explode($delimiter, $string))->trim()->filter()->toArrayValues();
    }
}
