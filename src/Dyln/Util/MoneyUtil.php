<?php

namespace Dyln\Util;

class MoneyUtil
{
    public static function toPence($amount)
    {
        $amount = round($amount, 2);

        return (int)bcmul($amount, 100);
    }

    public static function toFloat($amount)
    {
        $amount = (float)bcdiv($amount, 100, 2);

        return number_format(round($amount, 2), 2, '.', '');
    }

    public static function formatCurrency($valueInPence, $currency = null)
    {
        $value = MoneyUtil::toFloat($valueInPence);
        if (!$currency) {
            return $value;
        }
        $locale = self::currencyToLocale($currency);
        $formatter = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);
        $formatter->setAttribute(\NumberFormatter::ROUNDING_MODE, \NumberFormatter::ROUND_HALFUP);
        $return = $formatter->formatCurrency($value, $currency);

        return $return;
    }

    public static function currencyToLocale($currency)
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
