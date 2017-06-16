<?php

namespace Dyln\Validator;

use Dyln\Message\MessageFactory;

class UrlValidator extends AbstractValidator
{
    public function isValid($value)
    {
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            return MessageFactory::error(['message' => 'value is not valid url']);
        }

        return MessageFactory::success();
    }
}