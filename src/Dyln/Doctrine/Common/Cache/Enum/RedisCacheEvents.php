<?php

namespace Dyln\Doctrine\Common\Cache\Enum;

use Dyln\Enum;

class RedisCacheEvents extends Enum
{
    const BEFORE_COMMAND = 'dyln_doctrine_common_cache_redis/before_command';
    const AFTER_COMMAND = 'dyln_doctrine_common_cache_redis/after_command';
}
