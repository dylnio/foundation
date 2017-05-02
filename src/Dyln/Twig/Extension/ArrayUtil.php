<?php

namespace Dyln\Twig\Extension;

class ArrayUtil extends \Twig_Extension
{
    public function getName()
    {
        return 'arr';
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('arr', [$this, 'arr']),
        ];
    }

    public function arr(array $array, array $keys, $default = null)
    {
        return \Dyln\Util\ArrayUtil::getIn($array, $keys, $default);
    }
}