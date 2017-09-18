<?php

namespace Dyln\Session;

use Dyln\AppEnv;
use Dyln\Util\StringUtil;

class Session
{
    const DEFAULT_SEGMENT_NAME = 'default';
    protected $segments = [];
    protected $session;

    public function __construct($cookieParams = [])
    {
        self::init($cookieParams);
        if (AppEnv::isTest()) {
            return $this->session = [];
        } else {
            $this->session = &$_SESSION;
        }

        $this->extend();
    }

    public function extend()
    {
        if (AppEnv::isTest()) {
            return true;
        }
        $cookieName = ini_get('session.name');
        if (!empty($_COOKIE[$cookieName])) {
            $cookieParams = session_get_cookie_params();
            setcookie($cookieName, $_COOKIE[$cookieName], time() + (365 * 24 * 60 * 60), '/', $cookieParams["domain"]);
        }

        return true;
    }

    public static function init($cookieParams = [])
    {
        if (AppEnv::isTest()) {
            return true;
        }
        if (session_status() !== PHP_SESSION_ACTIVE) {
            ini_set('session.gc_maxlifetime', 365 * 24 * 60 * 60); // 1 year
            ini_set('session.cookie_lifetime', 365 * 24 * 60 * 60); // 1 year
            ini_set('session.gc_probability', 0);
            ini_set('session.gc_divisor', 100);
            ini_set('session.cookie_secure', false);
            ini_set('session.use_only_cookies', true);
            foreach ($cookieParams as $field => $value) {
                if ($field == 'name' && !trim($value)) {
                    $value = 'PHPSESSID';
                }
                ini_set('session.' . $field, $value);
            }
            $res = session_start();
            if (!$res) {
                throw new \Exception('session_start() failed');
            }
        }

        return true;
    }

    public function id()
    {
        if (AppEnv::isTest()) {
            return uniqid();
        }

        return session_id();
    }

    public function set($key, $value, int $ttl = 0)
    {
        $this->getDefaultSegment()->set($key, $value, $ttl);

        return true;
    }

    public function delete($keys)
    {
        if (!is_array($keys)) {
            $keys = [$keys];
        }
        $this->getDefaultSegment()->delete($keys);

        return true;
    }

    public function getDefaultSegment()
    {
        return $this->getSegment(self::DEFAULT_SEGMENT_NAME);
    }

    /**
     * @param string $name
     * @return Segment
     */
    public function getSegment($name = self::DEFAULT_SEGMENT_NAME)
    {
        $name = StringUtil::canonicalizeName($name);
        if (!isset($this->segments[$name])) {
            $this->segments[$name] = new Segment($name, $this->session);
        }

        return $this->segments[$name];
    }

    public function deleteSegment($name)
    {
        $this->getSegment($name)->destroy();
        unset($this->segments[$name]);
    }

    public function get($key, $default = null, $deleteAfter = false)
    {
        return $this->getDefaultSegment()->get($key, $default, $deleteAfter);
    }

}