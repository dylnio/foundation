<?php

namespace Dyln\CommandBus\Command\Handler\Resolver;

use Dyln\CommandBus\Command\Command;

class SimpleHandlerResolver implements HandlerResolver
{

    public function getHandlerClassName(Command $command)
    {
        $commandClassName = get_class($command); // App\User\Command\RegisterUserCommand
        $handlerClassName = str_replace('\\Command\\', '\\Command\\Handler\\', $commandClassName) . 'Handler'; // App\User\Command\Handler\RegisterUserCommandHandler

        return $handlerClassName;
    }
}