<?php

namespace Dyln\Database\Model\Formatter;

use Dyln\Database\Model\Formatter\Rules\Rules;

interface Formatter
{
    public function __construct(Rules $rules);
}
