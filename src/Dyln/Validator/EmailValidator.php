<?php
namespace Dyln\Validator;

use Dyln\Payload\PayloadFactory;

class EmailValidator extends AbstractValidator
{

    public function isValid($value)
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return PayloadFactory::createErrorPayload(['value is not a valid email address']);
        }

        return PayloadFactory::createSuccessPayload();
    }
}