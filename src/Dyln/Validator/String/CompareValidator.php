<?php
namespace Dyln\Validator\String;

use Dyln\Payload\PayloadFactory;
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

        return $value === $this->compareToValue ? PayloadFactory::createSuccessPayload() : PayloadFactory::createErrorPayload(['value does not match']);
    }
}