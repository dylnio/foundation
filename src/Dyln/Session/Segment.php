<?php

namespace Dyln\Session;

use Dyln\Util\ArrayUtil;
use Dyln\Util\StringUtil;

class Segment
{
    protected $name;
    protected $session;

    public function __construct($name, &$session)
    {
        $this->name = $this->cleanKey($name);
        $this->session = &$session;
        $this->session[$this->name] = isset($this->session[$this->name]) ? $this->session[$this->name] : [];
    }

    private function cleanKey($key)
    {
        return StringUtil::canonicalizeName($key);
    }

    public function getName()
    {
        return $this->name;
    }

    public function set($key, $value)
    {
        $this->session[$this->name][$this->cleanKey($key)] = $value;

        return $this;
    }

    public function get($key, $default = null, $deleteAfter = false)
    {
        $value = ArrayUtil::getIn($this->session, [$this->name, $this->cleanKey($key)], $default);
        if ($deleteAfter) {
            unset($this->session[$this->name][$this->cleanKey($key)]);
        }

        return $value;
    }

    public function delete($key)
    {
        unset($this->session[$this->name][$this->cleanKey($key)]);
    }
}