<?php

namespace Dyln\FlashMessenger\Enum;

use Dyln\Enum;

class MessageType extends Enum
{
    const SUCCESS = 'success';
    const ERROR = 'error';
    const WARNING = 'warning';
}
