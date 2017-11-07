<?php

namespace Dyln\ApiClient\Enum;

use Dyln\Enum;

class Events extends Enum
{
    const CALL_BEGIN = 'apiclient/call_begin';
    const BEFORE_CALL_SEND = 'apiclient/before_call_send';
    const AFTER_CALL_SEND = 'apiclient/after_call_send';
    const CALL_END = 'apiclient/call_end';
}
