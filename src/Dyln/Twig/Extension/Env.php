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
            new \Twig_SimpleFunction('env', [$this, 'env']),
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

    public function isAppLive()
    {
        return AppEnv::isLive();
    }
}