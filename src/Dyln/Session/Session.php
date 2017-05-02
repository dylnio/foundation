<?php

namespace Dyln\Session;

use Dyln\Util\StringUtil;

class Session
{
    const DEFAULT_SEGMENT_NAME = 'default';
    protected $segments = [];
    protected $session;

    public function __construct()
    {
        self::init();
        $this->session = &$_SESSION;
        $this->extend();
    }

    public function extend()
    {
        $cookieName = ini_get('session.name');
        if (!empty($_COOKIE[$cookieName])) {
            $cookieParams = session_get_cookie_params();
            setcookie($cookieName, $_COOKIE[$cookieName], time() + (365 * 24 * 60 * 60), '/', $cookieParams["domain"]);
        }

        return true;
    }

    public static function init()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            ini_set('session.gc_maxlifetime', 365 * 24 * 60 * 60); // 1 year
            ini_set('session.cookie_lifetime', 365 * 24 * 60 * 60); // 1 year
            ini_set('session.gc_probability', 1);
            ini_set('session.gc_divisor', 100);
            ini_set('session.cookie_secure', false);
            ini_set('session.use_only_cookies', true);
            session_start();
        }

        return true;
    }

    public function id()
    {
        return session_id();
    }

    public function set($key, $value)
    {
        $this->getDefaultSegment()->set($key, $value);

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

    public function get($key, $default = null, $deleteAfter = false)
    {
        return $this->getDefaultSegment()->get($key, $default, $deleteAfter);
    }

}