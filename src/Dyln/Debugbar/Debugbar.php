<?php

namespace Dyln\Debugbar;

use Dyln\Util\ArrayUtil;
use Psr\Log\LogLevel;

class Debugbar
{
    /** @var Debugbar */
    protected static $instance;
    protected $data = [];

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function _add($section, $data)
    {
        if (empty($data['app'])) {
            $data['app'] = defined('PROJECT_NAME') ? PROJECT_NAME : 'No project name';
        }
        $section = str_replace(' ', '_', $section);
        $existing = ArrayUtil::getIn($this->data, $section, []);
        $existing[] = $data;
        $this->data[$section] = $existing;
    }

    public function _getData()
    {
        return $this->data;
    }

    public static function log($message, $level = LogLevel::INFO)
    {
        self::getInstance()->_add('UserLog', ['level' => $level, 'message' => $message]);
    }

    public static function add($section, $data)
    {
        self::getInstance()->_add($section, $data);
    }

    public static function addBulk($data)
    {
        foreach ($data as $section => $rows) {
            foreach ($rows as $row) {
                self::getInstance()->_add($section, $row);
            }
        }
    }

    public static function getData()
    {
        return self::getInstance()->_getData();
    }
}
