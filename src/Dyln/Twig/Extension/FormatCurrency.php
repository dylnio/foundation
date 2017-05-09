<?php

namespace Dyln\Twig\Extension;

use Dyln\Util\MoneyUtil;

class FormatCurrency extends \Twig_Extension
{
    const APPEND = 'append';
    const PREPPEND = 'prepend';

    public function getName()
    {
        return 'formatcurrency';
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('formatCurrency', [$this, 'formatCurrency']),
        ];
    }

    public function formatCurrency($valueInPence, $currency = 'GBP')
    {
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
            return $symbol . ' ' . MoneyUtil::toFloat($valueInPence);
        } else {
            return MoneyUtil::toFloat($valueInPence) . ' ' . $symbol;
        }
    }
}