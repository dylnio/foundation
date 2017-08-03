<?php

namespace Dyln\Config;

use function Dyln\getin;

class Config
{
    static protected $config;

    static public function load($config = [])
    {
        self::$config = $config;
    }

    static public function overwrite($overwrite = [])
    {
        self::$config = array_merge(self::$config, $overwrite);
    }

    static public function get($key, $default = null)
    {
        return getin(self::$config, $key, $default);
    }

    static public function toArray()
    {
        return self::$config;
    }

}