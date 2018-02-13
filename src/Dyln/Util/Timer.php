<?php

namespace Dyln\Util;

class Timer
{
    protected static $start = 0;
    protected static $end = 0;

    public static function start()
    {
        self::$start = microtime(true);
        self::$end = 0;
    }

    public static function result()
    {
        self::$end = microtime(true);
        $time = self::$end - self::$start;

        return $time;
    }

    public static function getStart()
    {
        return self::$start;
    }

    public static function getEnd()
    {
        return self::$end;
    }
}
