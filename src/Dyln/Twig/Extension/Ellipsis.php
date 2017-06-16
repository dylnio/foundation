<?php

namespace Dyln\Twig\Extension;

class Ellipsis extends \Twig_Extension
{
    public function getName()
    {
        return 'ellipsis';
    }

    public function getFunctions()
    {
        /** @noinspection PhpUndefinedClassInspection */
        return [
            new \Twig_SimpleFunction('ellipsis', [$this, 'ellipsis']),
        ];
    }

    public function ellipsis($text, $length, $suffix = '...')
    {
        if (strlen($text) <= $length) {
            return $text;
        }

        return substr($text, 0, $length - strlen($suffix)) . $suffix;
    }
}