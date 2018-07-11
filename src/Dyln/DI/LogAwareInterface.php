<?php

namespace Dyln\DI;

use Dyln\Log\Logger;

interface LogAwareInterface
{
    public function setLogger(Logger $logger);

    public function getLogger();
}
