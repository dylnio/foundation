<?php

namespace Dyln\Util;

class ArrayUtil
{
    static public function getFirstElement(array $array, $remove = false)
    {
        if ($remove) {
            return array_shift($array);
        }

        return array_shift(array_values($array));
    }

    static public function getLastElement(array $array, $remove = false)
    {
        if ($remove) {
            return array_pop($array);
        }

        return array_pop(array_values($array));
    }

    /**
     * @param array $array
     * @param $keys
     * @param null $default
     * @return mixed
     */
    static public function getIn(array $array, $keys, $default = null)
    {
        if (is_null($keys)) {
            return $array;
        }
        if (!is_array($keys)) {
            $keys = [$keys];
        }

        $current = $array;
        foreach ($keys as $key) {
            if (!$current || !array_key_exists($key, $current)) {
                return $default;
            }

            $current = $current[$key];
        }

        return $current ?: $default;
    }

    static public function isAssoc(array $array)
    {
        if ([] === $array) return false;

        return array_keys($array) !== range(0, count($array) - 1);
    }
}