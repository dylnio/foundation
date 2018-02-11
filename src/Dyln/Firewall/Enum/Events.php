<?php

namespace Dyln\Firewall\Enum;

use Dyln\Enum;

class Events extends Enum
{
    const FW_PRE_CHECK = 'fw.pre.check';
    const FW_DENIED = 'fw.denied';
    const FW_NOT_LOGGED_IN = 'fw.not.logged.in';
    const FW_ATURHORIZED = 'fw.authorized';
    const FW_POST_CHECK = 'fw.post.check';
}
