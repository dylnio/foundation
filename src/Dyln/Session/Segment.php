<?php

namespace Dyln\Session;

use Dyln\Util\ArrayUtil;
use Dyln\Util\StringUtil;

class Segment
{
    const EXPIRY_KEY_NAME = '__expiry__';
    protected $name;
    protected $session;

    public function __construct($name, &$session)
    {
        $this->name = $this->cleanKey($name);
        $this->session = &$session;
        $this->session[$this->name] = isset($this->session[$this->name]) ? $this->session[$this->name] : [];
        $this->session[$this->name][self::EXPIRY_KEY_NAME] = $this->session[$this->name][self::EXPIRY_KEY_NAME] ?? [];
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
        $this->setKeyExpiry($key, $ttl);

        return $this;
    }

    public function has($key)
    {
        return array_key_exists($key, $this->session[$this->name]);
    }

    public function get($key, $default = null, $deleteAfter = false)
    {
        if ($this->isKeyExpired($key)) {
            $this->delete($key);

            return $default;
        }
        $value = ArrayUtil::getIn($this->session, [$this->name, $this->cleanKey($key)], $default);
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
            $this->deleteKeyExpiry($key);
        }
    }

    public function destroy()
    {
        unset($this->session[$this->name]);
    }

    private function isKeyExpired($key)
    {
        $expire = $this->session[$this->name][self::EXPIRY_KEY_NAME][$this->cleanKey($key)] ?? 0;
        if (!$expire) {
            return false;
        }

        return $expire < time();
    }

    private function setKeyExpiry($key, int $ttl = 0)
    {
        if (!$ttl) {
            $this->session[$this->name][self::EXPIRY_KEY_NAME][$this->cleanKey($key)] = 0;
        } else {
            $this->session[$this->name][self::EXPIRY_KEY_NAME][$this->cleanKey($key)] = time() + $ttl;
        }
    }

    private function deleteKeyExpiry($key)
    {
        if (isset($this->session[$this->name][self::EXPIRY_KEY_NAME][$this->cleanKey($key)])) {
            unset($this->session[$this->name][self::EXPIRY_KEY_NAME][$this->cleanKey($key)]);
        }
    }


}