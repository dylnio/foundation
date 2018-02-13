<?php

namespace Dyln\Validator\String;

use Dyln\Message\MessageFactory;
use Dyln\Validator\AbstractValidator;

class CompareValidator extends AbstractValidator
{
    protected $compareToValue;

    public function __construct($compareToValue)
    {
        $this->compareToValue = $compareToValue;
    }

    public function isValid($value)
    {
        return $value === $this->compareToValue ? MessageFactory::success() : MessageFactory::error(['message' => 'value does not match']);
    }
}
