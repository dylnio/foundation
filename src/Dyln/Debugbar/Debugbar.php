<?php

namespace Dyln\Debugbar;

use Dyln\Util\ArrayUtil;

class Debugbar
{
    static protected $data = [];

    static public function add($section, $data)
    {
        $existing = ArrayUtil::getIn(self::$data, $section, []);
        $existing[] = $data;
        self::$data[$section] = $existing;
    }

    static public function addBulk($data)
    {
        foreach ($data as $section => $rows) {
            $existing = ArrayUtil::getIn(self::$data, $section, []);
            foreach ($rows as $row) {
                $existing[] = $row;
            }
            self::$data[$section] = $existing;
        }
    }

    static public function getData()
    {
        return self::$data;
    }
}