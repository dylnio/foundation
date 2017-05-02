<?php

namespace Dyln\Util;

class Timer
{
    static protected $start = 0;

    static public function start()
    {
        self::$start = microtime(true);
    }

    static public function result()
    {
        $time = microtime(true) - self::$start;
        self::$start = 0;

        return $time;
    }
}