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
        /** @noinspection PhpUndefinedClassInspection */
        return [
            new \Twig_SimpleFunction('formatCurrency', [$this, 'formatCurrency']),
        ];
    }

    public function formatCurrency($valueInPence, $currency = null, $hideSymbol = false)
    {
        return MoneyUtil::formatCurrency($valueInPence, $currency, $hideSymbol);
    }
}