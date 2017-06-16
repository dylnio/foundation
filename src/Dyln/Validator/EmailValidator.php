<?php

namespace Dyln\Validator;

use Dyln\Message\MessageFactory;

class EmailValidator extends AbstractValidator
{

    public function isValid($value)
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return MessageFactory::error(['message' => 'value is not a valid email address']);
        }

        return MessageFactory::success();
    }
}