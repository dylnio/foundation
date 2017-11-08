<?php

namespace Dyln\DI;

use Doctrine\Common\Cache\CacheProvider;

interface CacheAwareInterface
{
    public function setCache(CacheProvider $cache);

    public function getCache() : CacheProvider;
}
