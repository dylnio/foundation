<?php

namespace Dyln\Config;

use function Dyln\getin;
use function Dyln\has;

class Config
{
    static protected $config = [];
    static protected $overwrite = [];

    static public function load($config = [])
    {
        self::$config = $config;
    }

    static public function overwrite($overwrite = [])
    {
        self::$overwrite = $overwrite;
    }

    static public function merge($merge = [])
    {
        self::$config = array_replace_recursive(self::$config, $merge);
    }

    static public function get($key, $default = null)
    {
        $value = getin(self::$config, $key, $default);
        if (has(self::$overwrite, $key)) {
            $overwriteValue = getin(self::$overwrite, $key, $default);
            if (is_array($value)) {
                $value = array_replace_recursive($value, $overwriteValue);
            } else {
                $value = $overwriteValue;
            }
        }

        return $value;
    }

    static public function toArray()
    {
        array_replace_recursive(self::$config, self::$overwrite);
    }

}