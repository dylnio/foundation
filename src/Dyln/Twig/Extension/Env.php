<?php

namespace Dyln\Twig\Extension;

use Dyln\AppEnv;

class Env extends \Twig_Extension
{
    public function getName()
    {
        return 'env';
    }

    public function getFunctions()
    {
        /** @noinspection PhpUndefinedClassInspection */
        return [
            new \Twig_SimpleFunction('getAppEnv', [$this, 'getAppEnv']),
            new \Twig_SimpleFunction('isAppLive', [$this, 'isAppLive']),
            new \Twig_SimpleFunction('isAppDev', [$this, 'isAppDev']),
            new \Twig_SimpleFunction('isUrlKeyMatch', [$this, 'isUrlKeyMatch']),
            new \Twig_SimpleFunction('env', [$this, 'env']),
            new \Twig_SimpleFunction('cookie', [$this, 'cookie']),
        ];
    }

    public function getAppEnv()
    {
        return AppEnv::getAppEnv();
    }

    public function env($key, $default = null)
    {
        return AppEnv::env($key, $default);
    }

    public function cookie($key, $default = null)
    {
        return AppEnv::cookie($key, $default);
    }

    public function isAppLive()
    {
        return AppEnv::isLive();
    }

    public function isAppDev()
    {
        return !AppEnv::isLive();
    }

    public function isUrlKeyMatch($key, $value)
    {
        return AppEnv::isUrlKeyMatch($key, $value);
    }
}
