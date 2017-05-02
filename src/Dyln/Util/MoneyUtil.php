<?php

namespace Dyln\Util;

class MoneyUtil
{
    static public function toPence($amount)
    {
        return (int)bcmul($amount, 100);
    }

    static public function toFloat($amount)
    {
        return (float)bcdiv($amount, 100, 2);
    }
}