<?php
namespace Dyln\Validator;

use Dyln\Payload\PayloadFactory;

class UrlValidator extends AbstractValidator
{

    public function isValid($value)
    {
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            return PayloadFactory::createErrorPayload(['value is not valid url']);
        }

        return PayloadFactory::createSuccessPayload();
    }
}