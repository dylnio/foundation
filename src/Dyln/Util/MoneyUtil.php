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

    static public function formatCurrency($valueInPence, $currency = null, $hideSymbol = false)
    {
        $value = MoneyUtil::toFloat($valueInPence);
        if (!$currency) {
            return $value;
        }
        $format = '%.2n';
        switch (strtoupper($currency)) {
            case 'GBP':
                setlocale(LC_MONETARY, 'en_GB.UTF-8');
                $format = '£ %!n';
                if ($hideSymbol) {
                    $format = '%!n';
                }
                break;
            case 'USD':
                setlocale(LC_MONETARY, 'en_US.UTF-8');
                $format = '$ %!n';
                if ($hideSymbol) {
                    $format = '%!n';
                }
                break;
            case 'EUR':
                setlocale(LC_MONETARY, 'de_DE.UTF-8');
                $format = '%!n €';
                if ($hideSymbol) {
                    $format = '%!n';
                }
                break;
        }

        return money_format($format, $value);
    }
}