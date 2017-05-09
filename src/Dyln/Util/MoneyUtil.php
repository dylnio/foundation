<?php

namespace Dyln\Util;

class MoneyUtil
{
    static public function toPence($amount)
    {
        $amount = round($amount, 2);

        return (int)bcmul($amount, 100);
    }

    static public function toFloat($amount)
    {
        $amount = (float)bcdiv($amount, 100, 2);

        return number_format(round($amount, 2), 2);
    }
}