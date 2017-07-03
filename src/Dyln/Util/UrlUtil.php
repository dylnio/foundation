<?php

namespace Dyln\Util;

class UrlUtil
{
    static public function getBaseUrl()
    {
        $host = $_SERVER['HTTP_HOST'] ?? null;
        $https = $_SERVER['HTTPS'] ?? false;

        return BooleanUtil::getBool($https) ? 'https://' . $host : 'http://' . $host;
    }

    static public function getCurrentUrl()
    {
        $uri = $_SERVER['REQUEST_URI'] ?? null;

        return self::getBaseUrl() . $uri;
    }
}