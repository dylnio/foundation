<?php

namespace Dyln\CommandBus\Command;

interface Command
{
    public function get($param, $default = null);

    public function getAllParams();

    public function keyExists($key);

    public function removeParamPrefix($prefix);

    /**
     * @param $key
     * @param $value
     * @return self
     */
    public function set($key, $value);

    public function has($key);

    public function isEmpty($key);
}