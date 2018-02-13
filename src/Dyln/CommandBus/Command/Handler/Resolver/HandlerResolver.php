<?php

namespace Dyln\CommandBus\Command\Handler\Resolver;

use Dyln\CommandBus\Command\Command;

interface HandlerResolver
{
    public function getHandlerClassName(Command $command);
}
