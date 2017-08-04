<?php

namespace Dyln\Twig\Extension;


class Config extends \Twig_Extension
{
    public function getName()
    {
        return 'config';
    }

    public function getFunctions()
    {
        /** @noinspection PhpUndefinedClassInspection */
        return [
            new \Twig_SimpleFunction('config', [$this, 'config']),
        ];
    }

    public function config($key, $default = null)
    {
        return \Dyln\Config\Config::get($key, $default);
    }
}