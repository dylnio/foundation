<?php

namespace Dyln\Session;

use Dyln\Util\ArrayUtil;
use Dyln\Util\StringUtil;

class Segment
{
    const EXPIRY_SEGMENT_NAME = 'expiry';
    protected $name;
    protected $session;

    public function __construct($name, &$session)
    {
        $this->name = $this->cleanKey($name);
        $this->session = &$session;
        $this->session[$this->name] = isset($this->session[$this->name]) ? $this->session[$this->name] : [];
        $this->session[self::EXPIRY_SEGMENT_NAME] = [];
    }

    private function cleanKey($key)
    {
        return StringUtil::canonicalizeName($key);
    }

    public function getName()
    {
        return $this->name;
    }

    public function set($key, $value, int $ttl = 0)
    {
        $this->session[$this->name][$this->cleanKey($key)] = $value;
        if ($ttl > 0) {
            $this->session[self::EXPIRY_SEGMENT_NAME][$this->cleanKey($key)] = time() + $ttl;
        } else {
            if (array_key_exists($this->cleanKey($key), $this->session[self::EXPIRY_SEGMENT_NAME])) {
                unset($this->session[self::EXPIRY_SEGMENT_NAME][$this->cleanKey($key)]);
            }
        }

        return $this;
    }

    public function has($key)
    {
        return array_key_exists($key, $this->session[$this->name]);
    }

    public function get($key, $default = null, $deleteAfter = false)
    {
        $value = ArrayUtil::getIn($this->session, [$this->name, $this->cleanKey($key)], $default);
        $expiry = $this->session[self::EXPIRY_SEGMENT_NAME][$this->cleanKey($key)] ?? null;
        if ($expiry && $expiry < time()) {
            $this->delete($key);

            return $default;
        }
        if ($deleteAfter) {
            $this->delete($key);
        }

        return $value;
    }

    public function delete($keys)
    {
        if (!is_array($keys)) {
            $keys = [$keys];
        }
        foreach ($keys as $key) {
            unset($this->session[$this->name][$this->cleanKey($key)]);
            unset($this->session[self::EXPIRY_SEGMENT_NAME][$this->cleanKey($key)]);
        }
    }

    public function destroy()
    {
        unset($this->session[$this->name]);
    }
}