<?php

namespace Dyln\Validator;

use Dyln\Message\MessageFactory;

class NotEmptyValidator extends AbstractValidator
{
    public function isValid($value)
    {
        if ($value === null || $value === '') {
            return MessageFactory::error(['message' => 'value cannot be empty']);
        }

        return MessageFactory::success();
    }
}
