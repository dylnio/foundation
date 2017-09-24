<?php

namespace Dyln\CommandBus\Command;

interface Command
{
    public function get($param, $default = null);

    public function getParams();

    public function set($key, $value);

    public function has($key);
}
