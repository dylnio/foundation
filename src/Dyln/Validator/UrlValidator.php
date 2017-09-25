<?php

namespace Dyln\Validator;

use Dyln\Message\MessageFactory;

class UrlValidator extends AbstractValidator
{
    public function isValid($value)
    {
        $urlPattern = '/(?:https?:\/\/)?(?:[a-zA-Z0-9.-]+?\.(?:[a-zA-Z])|\d+\.\d+\.\d+\.\d+)/i';
        if (!preg_match($urlPattern, $value)) {
            return MessageFactory::error(['message' => 'value is not valid url']);
        }

        return MessageFactory::success();
    }
}
