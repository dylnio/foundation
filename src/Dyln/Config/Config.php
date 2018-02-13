<?php

namespace Dyln\Config;

use function Dyln\getin;
use function Dyln\has;
use function Dyln\set;

class Config
{
    protected static $config = [];
    protected static $overwrite = [];
    protected static $manual = [];

    public static function load($config = [])
    {
        self::$config = $config;
    }

    public static function loadFromFiles($files = [])
    {
        foreach ($files as $file) {
            self::merge(require_once $file);
        }
    }

    public static function overwrite($overwrite = [])
    {
        self::$overwrite = $overwrite;
    }

    public static function merge($merge = [])
    {
        self::$config = array_replace_recursive(self::$config, $merge);
    }

    public static function get($key, $default = null)
    {
        $value = getin(self::$config, $key, $default);
        if (has(self::$manual, $key)) {
            $manualValue = getin(self::$manual, $key, $default);
            $value = is_array($value) ? array_replace_recursive($value, $manualValue) : $manualValue;
        } elseif (has(self::$overwrite, $key)) {
            $overwriteValue = getin(self::$overwrite, $key, $default);
            $value = is_array($value) ? array_replace_recursive($value, $overwriteValue) : $overwriteValue;
        }

        return $value;
    }

    public static function set($key, $value)
    {
        $array = self::$manual;
        set($array, $key, $value);
        self::$manual = $array;
    }

    public static function toArray()
    {
        array_replace_recursive(self::$config, self::$overwrite);
    }

    public static function value($value)
    {
    }
}
