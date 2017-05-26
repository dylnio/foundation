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
        return MoneyUtil::formatCurrency($valueInPence, $currency);
    }
}