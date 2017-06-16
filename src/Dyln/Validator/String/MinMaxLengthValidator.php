<?php

namespace Dyln\Validator\String;

use Dyln\Message\MessageFactory;
use Dyln\Validator\AbstractValidator;

class MinMaxLengthValidator extends AbstractValidator
{

    protected $min;
    protected $max;

    public function __construct($min, $max = null)
    {
        $this->min = $min;
        $this->max = $max;
    }

    public function isValid($value)
    {
        if (strlen($value) < $this->min) {
            return MessageFactory::error(['message' => 'value length cannot be less than ' . $this->min]);
        }

        if ($this->max) {
            if (strlen($value) > $this->max) {
                return MessageFactory::error(['message' => 'value length cannot be more than ' . $this->max]);
            }
        }

        return MessageFactory::success();
    }
}