<?php

namespace Dyln\Event;

class Emitter extends \League\Event\Emitter
{
    public function getEvents()
    {
        return array_keys($this->listeners);
    }
}
