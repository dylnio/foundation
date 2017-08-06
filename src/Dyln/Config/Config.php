<?php

namespace Dyln\Config;

use function Dyln\getin;
use function Dyln\has;

class Config
{
    static protected $config = [];
    static protected $overwrite = [];
    static protected $manual = [];

    static public function load($config = [])
    {
        self::$config = $config;
    }

    static public function loadFromFiles($files = [])
    {
        foreach ($files as $file) {
            self::merge(require_once $file);
        }
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
        if (has(self::$manual, $key)) {
            $manualValue = getin(self::$manual, $key, $default);
            $value = is_array($value) ? array_replace_recursive($value, $manualValue) : $manualValue;
        } else if (has(self::$overwrite, $key)) {
            $overwriteValue = getin(self::$overwrite, $key, $default);
            $value = is_array($value) ? array_replace_recursive($value, $overwriteValue) : $overwriteValue;
        }

        return $value;
    }

    static public function set($key, $value)
    {
        self::$manual[$key] = $value;
    }

    static public function toArray()
    {
        array_replace_recursive(self::$config, self::$overwrite);
    }

    static public function value($value)
    {

    }
}