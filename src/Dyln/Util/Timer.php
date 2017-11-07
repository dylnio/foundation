<?php

namespace Dyln\Util;

class Timer
{
    static protected $start = 0;
    static protected $end = 0;

    static public function start()
    {
        self::$start = microtime(true);
        self::$end = 0;
    }

    static public function result()
    {
        self::$end = microtime(true);
        $time = self::$end - self::$start;
        return $time;
    }

    static public function getStart()
    {
        return self::$start;
    }

    static public function getEnd()
    {
        return self::$end;
    }
}
