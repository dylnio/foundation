<?php

namespace Dyln\Debugbar;

use Dyln\Util\ArrayUtil;
use Psr\Log\LogLevel;

class Debugbar
{
    protected static $data = [];

    public static function log($message, $level = LogLevel::INFO)
    {
        self::add('UserLog', ['level' => $level, 'message' => $message]);
    }

    public static function add($section, $data)
    {
        $section = str_replace(' ', '_', $section);
        $existing = ArrayUtil::getIn(self::$data, $section, []);
        $existing[] = $data;
        self::$data[$section] = $existing;
    }

    public static function addBulk($data)
    {
        foreach ($data as $section => $rows) {
            $existing = ArrayUtil::getIn(self::$data, $section, []);
            foreach ($rows as $row) {
                $existing[] = $row;
            }
            self::$data[$section] = $existing;
        }
    }

    public static function getData()
    {
        return self::$data;
    }
}
