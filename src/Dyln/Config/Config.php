<?php

namespace Dyln\Config;

use function Dyln\getin;

class Config
{
    protected $config;

    /**
     * Config constructor.
     * @param $config
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    public function get($key, $default = null)
    {
        return getin($this->config, $key, $default);
    }

    public function getSub($key)
    {
        $value = $this->get($key, []);
        if (!is_array($value)) {
            throw new \Exception('new value is not array');
        }
        return new self($value);
    }

    public function toArray()
    {
        return $this->config;
    }

}