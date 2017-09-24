<?php

namespace Dyln\CommandBus;

use Dyln\CommandBus\Command\Command;
use Dyln\CommandBus\Command\Handler\Handler;
use Dyln\CommandBus\Command\Handler\HandlerWithEmitter;
use Dyln\CommandBus\Command\Handler\Resolver\HandlerResolver;
use Dyln\CommandBus\Exception\HandlerNotFoundException;
use Dyln\Event\Emitter;
use Dyln\Message\Message;
use Interop\Container\ContainerInterface;

class Bus
{
    protected $handlerResolvers = [];
    protected $emitter;
    /** @var ContainerInterface */
    protected $container;

    public function __construct($handlerResolvers = [], Emitter $emitter, ContainerInterface $container)
    {
        $this->handlerResolvers = $handlerResolvers;
        $this->emitter = $emitter;
        $this->container = $container;
    }

    public function execute(Command $command)
    {
        /** @var Handler $handler */
        $handler = $this->getHandler($command);
        if (!$handler) {
            throw new HandlerNotFoundException();
        }

        /** @var Message $paylod */
        $paylod = $handler->handle($command);
        $eventsTriggered = $handler->getEventsTriggered();
        foreach ($eventsTriggered as $eventData) {
            foreach ($eventData as $data) {
                $this->emitter->emit($data['event'], $data['params']);
            }
        }
        $handler->resetEventsTriggered();

        return $paylod;

    }

    /**
     * @param Command $command
     * @return Handler
     */
    private function getHandler(Command $command)
    {
        $handler = null;
        /** @var HandlerResolver $resolver */
        foreach ($this->handlerResolvers as $resolver) {
            if ($handlerClassName = $resolver->getHandlerClassName($command)) {
                $handler = $this->container->get($handlerClassName);
                if ($handler instanceof HandlerWithEmitter) {
                    $handler->setEmitter($this->emitter);
                }
            }
        }

        return $handler;
    }
}
