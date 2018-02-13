<?php

namespace Dyln\Twig\Extension;

class BooleanUtil extends \Twig_Extension
{
    public function getName()
    {
        return 'boolutil';
    }

    public function getFunctions()
    {
        /** @noinspection PhpUndefinedClassInspection */
        return [
            new \Twig_SimpleFunction('bool', [$this, 'bool']),
        ];
    }

    public function bool($value)
    {
        return \Dyln\Util\BooleanUtil::getBool($value);
    }
}
