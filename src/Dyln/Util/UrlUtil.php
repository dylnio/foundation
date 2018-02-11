<?php

namespace Dyln\Util;

class UrlUtil
{
    public static function getBaseUrl($forceHttps = false)
    {
        $host = $_SERVER['HTTP_HOST'] ?? null;
        $https = $_SERVER['HTTPS'] ?? false;
        if ($forceHttps) {
            $https = true;
        }

        return BooleanUtil::getBool($https) ? 'https://' . $host : 'http://' . $host;
    }

    public static function getCurrentUrl()
    {
        $uri = $_SERVER['REQUEST_URI'] ?? null;

        return self::getBaseUrl() . $uri;
    }

    public static function generate($path = null, $forceHttps = false)
    {
        $path = ltrim(trim($path), '/');
        $base = self::getBaseUrl($forceHttps);
        if (!$path) {
            return $base;
        }

        return trim($base . '/' . $path, '/');
    }
}
