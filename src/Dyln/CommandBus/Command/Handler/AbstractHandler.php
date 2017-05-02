<?php

namespace Dyln\CommandBus\Command\Handler;

use Dyln\CommandBus\Command\Command;
use Dyln\Event\Emitter;
use League\Event\Event;

abstract class AbstractHandler implements Handler, HandlerWithEmitter
{
    /** @var  Emitter */
    private $emitter;
    /** @var Event[] */
    protected $eventsTriggered = [];

    abstract public function handle(Command $command);

    public function getEventsTriggered()
    {
        return $this->eventsTriggered;
    }

    public function addToEventsTriggered(Event $event, $params = [])
    {
        $this->eventsTriggered[$event->getName()][] = [
            'event'  => $event,
            'params' => $params,
        ];
    }

    public function resetEventsTriggered()
    {
        $this->eventsTriggered = [];
    }

    public function setEmitter(Emitter $emitter)
    {
        $this->emitter = $emitter;
    }

    public function getEmitter(): Emitter
    {
        return $this->emitter;
    }

    public function emit(\Dyln\Event\Event $event, $params = [])
    {
        $this->emitter->emit($event, $params);
    }
}