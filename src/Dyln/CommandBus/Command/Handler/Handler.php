<?php

namespace Dyln\CommandBus\Command\Handler;

use Dyln\CommandBus\Command\Command;
use Dyln\Message\Message;
use League\Event\Event;

interface Handler
{
    public function handle(Command $command): Message;

    public function getEventsTriggered();

    public function addToEventsTriggered(Event $event);

    public function resetEventsTriggered();
}
