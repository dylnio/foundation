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
            new \Twig_SimpleFunction('json_encode', [$this, '_json_encode']),
            new \Twig_SimpleFunction('url_encode', [$this, '_url_encode']),
        ];
    }

    public function _json_decode($data, $assoc = false, $depth = 512, $options = 0)
    {
        return json_decode($data, $assoc, $depth, $options);
    }

    public function _json_encode($value, $options = 0, $depth = 512)
    {
        return json_encode($value, $options, $depth);
    }

    public function _url_encode($value)
    {
        return urlencode($value);
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
