<?php

namespace Dyln\Twig\Extension;

use Dyln\Util\MoneyUtil;

class FormatCurrency extends \Twig_Extension
{
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
        switch (strtoupper($currency)) {
            case 'GBP':
                $symbol = '£';
                break;
            case 'USD':
                $symbol = '$';
                break;
        }

        return $symbol . ' ' . MoneyUtil::toFloat($valueInPence);
    }
}