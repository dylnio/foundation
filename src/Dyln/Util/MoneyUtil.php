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

        return number_format(round($amount, 2), 2, '.', '');
    }

    static public function formatCurrency($valueInPence, $currency = null)
    {
        $value = MoneyUtil::toFloat($valueInPence);
        if (!$currency) {
            return $value;
        }
        $locale = self::currencyToLocale($currency);
        $formatter = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);
        $return = $formatter->formatCurrency($value, $currency);

        return $return;
    }

    static public function currencyToLocale($currency)
    {
        switch (strtolower($currency)) {
            case 'eur':
                return 'fr_FR';
            case 'gbp':
                return 'en_GB';
            default:
                return 'en_US';
        }
    }
}