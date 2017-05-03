<?php

namespace Dyln\Firewall\Enum;

use Dyln\Enum;

class Roles extends Enum
{
    const GUEST = 'ROLE_GUEST';
    const MEMBER = 'ROLE_MEMBER';
    const ADMIN = 'ROLE_ADMIN';
}