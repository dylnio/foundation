<?php

namespace Dyln\CommandBus\Command;

use function Dyln\getin;
use function Dyln\has;

abstract class AbstractCommand implements Command
{
    protected $params = [];

    public function __construct(array $params)
    {
        $this->params = $params;
    }

    public function get($key, $default = null)
    {
        return getin($this->params, $key, $default);
    }

    public function getParams()
    {
        return $this->params;
    }

    public function set($key, $value)
    {
        $this->params[$key] = $value;

        return $this;
    }

    public function has($key)
    {
        return has($this->params, $key);
    }
}
