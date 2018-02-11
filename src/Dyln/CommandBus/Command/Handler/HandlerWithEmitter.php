<?php

namespace Dyln\CommandBus\Command\Handler;

use Dyln\Event\Emitter;
use Dyln\Event\Event;

interface HandlerWithEmitter
{
    public function setEmitter(Emitter $emitter);

    public function getEmitter(): Emitter;

    public function emit(Event $event, $params = []);
}
