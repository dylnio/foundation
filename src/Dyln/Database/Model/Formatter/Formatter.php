<?php

namespace Dyln\Model\Formatter;

use Dyln\Model\Formatter\Rules\Rules;

interface Formatter
{
    public function __construct(Rules $rules);
}