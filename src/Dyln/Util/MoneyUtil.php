<?php

namespace Dyln\Util;

class MoneyUtil
{
    const APPEND = 'append';
    const PREPPEND = 'prepend';

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

    static public function formatCurrency($valueInPence, $currency = null)
    {
        $value = MoneyUtil::toFloat($valueInPence);
        if (!$currency) {
            return $value;
        }
        $symbol = '';
        $placement = self::PREPPEND;
        switch (strtoupper($currency)) {
            case 'GBP':
                $symbol = '£';
                $placement = self::PREPPEND;
                break;
            case 'USD':
                $symbol = '$';
                $placement = self::PREPPEND;
                break;
            case 'EUR':
                $symbol = '€';
                $placement = self::APPEND;
                break;
        }
        if ($placement == self::PREPPEND) {
            return $symbol . ' ' . $value;
        } else {
            return $value . ' ' . $symbol;
        }
    }
}