<?php

namespace Dyln\DI;

use Dyln\Event\Emitter;

interface EventEmitterAwareInterface
{
    public function setEmitter(Emitter $emitter);

    public function getEmitter();
}
