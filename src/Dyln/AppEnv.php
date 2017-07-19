<?php

namespace Dyln;

use Dyln\Util\BooleanUtil;

class AppEnv
{
    const DEFAULT_ENV = 'default';
    const LIVE_ENV = 'production';

    static private $placeholders = [
        'ROOT_DIR',
        'APPLICATION_ENV',
    ];
    static public $serverToEnvMap = [];

    static public function isDev()
    {
        return self::getAppEnv() !== self::LIVE_ENV;
    }

    static public function getAppEnv()
    {
        if (!defined('APPLICATION_ENV')) {
            $env = getenv('APPLICATION_ENV') ?? get_cfg_var('APPLICATION_ENV') ?? self::getAppEnvFromServerName();
            if (!$env) {
                throw new \Exception('APPLICATION_ENV is not set');
            }
            define('APPLICATION_ENV', $env);
        }
        putenv('APPLICATION_ENV=' . APPLICATION_ENV);
        $_ENV['APPLICATION_ENV'] = APPLICATION_ENV;
        $_SERVER['APPLICATION_ENV'] = APPLICATION_ENV;

        return APPLICATION_ENV;
    }

    static public function getAppEnvFromServerName()
    {
        if (php_sapi_name() == 'cli') {
            $servername = gethostname();
        } else {
            $servername = $_SERVER['SERVER_NAME'];
        }
        $list = self::$serverToEnvMap[self::LIVE_ENV] ?? [];
        if (in_array($servername, $list)) {
            return self::LIVE_ENV;
        }

        return self::DEFAULT_ENV;
    }

    static public function isLive()
    {
        return self::getAppEnv() == self::LIVE_ENV;
    }

    static public function env($key, $default = null)
    {
        $value = getenv($key);
        if (!$value) {
            return $default;
        }
        if (strpos($value, '{{') !== false) {
            foreach (self::$placeholders as $placeholder) {
                $value = str_replace('{{' . $placeholder . '}}', self::env($placeholder), $value);
            }
        }

        return $value;
    }

    static public function isDebugEnabled()
    {
        $debug = BooleanUtil::getBool(AppEnv::env('app.debug', false));
        if (!$debug) {
            $overwrite = $_GET['debug'] ?? $_COOKIE['debug'] ?? null;
            if ($overwrite) {
                $debug = $overwrite === AppEnv::env('app.debug.url_key');
            }
        }

        return $debug;
    }

    static public function isDebugBarEnabled()
    {
        if (!self::isDebugEnabled()) {
            return false;
        }
        $bar = BooleanUtil::getBool(AppEnv::env('app.debugbar', false));
        if (!$bar) {
            $overwrite = $_GET['debug_bar'] ?? $_COOKIE['debug_bar'] ?? null;
            if ($overwrite) {
                $bar = $overwrite === AppEnv::env('app.debug.url_key');
            }
        }

        return $bar;
    }
}