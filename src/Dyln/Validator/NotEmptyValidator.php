<?php
namespace Dyln\Validator;

use Dyln\Payload\PayloadFactory;

class NotEmptyValidator extends AbstractValidator
{

    public function isValid($value)
    {
        if ($value === null || $value === '') {
            return PayloadFactory::createErrorPayload(['value cannot be empty']);
        }

        return PayloadFactory::createSuccessPayload();
    }
}