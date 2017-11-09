<?php

namespace Dyln\Twig\Extension;

class Functions extends \Twig_Extension
{
    public function getName()
    {
        return 'functions';
    }

    public function getFunctions()
    {
        /** @noinspection PhpUndefinedClassInspection */
        return [
            new \Twig_SimpleFunction('md5', [$this, '_md5']),
            new \Twig_SimpleFunction('gavatar', [$this, '_gavatar']),
            new \Twig_SimpleFunction('json_decode', [$this, '_json_decode']),
        ];
    }

    public function _json_decode($data, $assoc = false, $depth = 512, $options = 0)
    {
        return json_decode($data, $assoc, $depth, $options);
    }

    public function _md5($value)
    {
        return md5($value);
    }

    public function _gavatar($value)
    {
        $value = md5(str_replace(' ', '_', strtolower($value)));

        return 'https://www.gravatar.com/avatar/' . $value . '?s=48&d=identicon';
    }
}
