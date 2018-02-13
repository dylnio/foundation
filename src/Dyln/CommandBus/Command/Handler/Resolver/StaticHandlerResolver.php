<?php

namespace Dyln\CommandBus\Command\Handler\Resolver;

use Dyln\CommandBus\Command\Command;

class StaticHandlerResolver implements HandlerResolver
{
    protected $map = [];

    public function add($commandClassName, $handlerClassName)
    {
        $this->map[$commandClassName] = $handlerClassName;

        return $this;
    }

    public function getHandlerClassName(Command $command)
    {
        if (isset($this->map[get_class($command)])) {
            return $this->map[get_class($command)];
        }

        return false;
    }
}
